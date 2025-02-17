<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Edge;
use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Exception;
use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Graph;
use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Node;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputException;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\CoveredRuleInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Supportive\OutputFormatter\Configuration\ConfigurationGraphViz;
use Deptrac\Deptrac\Supportive\OutputFormatter\Configuration\FormatterConfiguration;
use function sys_get_temp_dir;
use function tempnam;
abstract class GraphVizOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var array{hidden_layers?: string[], groups?: array<string, string[]>, point_to_groups?: bool}
     */
    private readonly array $config;
    public function __construct(FormatterConfiguration $config)
    {
        /** @var array{hidden_layers?: string[], groups?: array<string, string[]>, point_to_groups?: bool}  $extractedConfig */
        $extractedConfig = $config->getConfigFor('graphviz');
        $this->config = $extractedConfig;
    }
    public function finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $layerViolations = $this->calculateViolations($result->violations());
        $layersDependOnLayers = $this->calculateLayerDependencies($result->allRules());
        $outputConfig = ConfigurationGraphViz::fromArray($this->config);
        $graph = Graph::create('');
        if ($outputConfig->pointToGroups) {
            $graph->setAttribute('compound', 'true');
        }
        $nodes = $this->createNodes($outputConfig, $layersDependOnLayers);
        $this->addNodesToGraph($graph, $nodes, $outputConfig);
        $this->connectEdges($graph, $nodes, $outputConfig, $layersDependOnLayers, $layerViolations);
        $this->output($graph, $output, $outputFormatterInput);
    }
    /**
     * @param Violation[] $violations
     *
     * @return array<string, array<string, int>>
     */
    private function calculateViolations(array $violations) : array
    {
        $layerViolations = [];
        foreach ($violations as $violation) {
            if (!isset($layerViolations[$violation->getDependerLayer()])) {
                $layerViolations[$violation->getDependerLayer()] = [];
            }
            if (!isset($layerViolations[$violation->getDependerLayer()][$violation->getDependentLayer()])) {
                $layerViolations[$violation->getDependerLayer()][$violation->getDependentLayer()] = 1;
            } else {
                ++$layerViolations[$violation->getDependerLayer()][$violation->getDependentLayer()];
            }
        }
        return $layerViolations;
    }
    /**
     * @param RuleInterface[] $rules
     *
     * @return array<string, array<string, int>>
     */
    private function calculateLayerDependencies(array $rules) : array
    {
        $layersDependOnLayers = [];
        foreach ($rules as $rule) {
            if ($rule instanceof CoveredRuleInterface) {
                $layerA = $rule->getDependerLayer();
                $layerB = $rule->getDependentLayer();
                if (!isset($layersDependOnLayers[$layerA])) {
                    $layersDependOnLayers[$layerA] = [];
                }
                if (!isset($layersDependOnLayers[$layerA][$layerB])) {
                    $layersDependOnLayers[$layerA][$layerB] = 1;
                    continue;
                }
                ++$layersDependOnLayers[$layerA][$layerB];
            } elseif ($rule instanceof Uncovered) {
                if (!isset($layersDependOnLayers[$rule->layer])) {
                    $layersDependOnLayers[$rule->layer] = [];
                }
            }
        }
        return $layersDependOnLayers;
    }
    /**
     * @param array<string, array<string, int>> $layersDependOnLayers
     *
     * @return Node[]
     */
    private function createNodes(ConfigurationGraphViz $outputConfig, array $layersDependOnLayers) : array
    {
        $nodes = [];
        foreach ($layersDependOnLayers as $layer => $layersDependOn) {
            if (\in_array($layer, $outputConfig->hiddenLayers, \true)) {
                continue;
            }
            if (!isset($nodes[$layer])) {
                $nodes[$layer] = new Node($layer);
            }
            foreach ($layersDependOn as $layerDependOn => $_) {
                if (\in_array($layerDependOn, $outputConfig->hiddenLayers, \true)) {
                    continue;
                }
                if (!isset($nodes[$layerDependOn])) {
                    $nodes[$layerDependOn] = new Node($layerDependOn);
                }
            }
        }
        return $nodes;
    }
    /**
     * @param Node[] $nodes
     * @param array<string, array<string, int>> $layersDependOnLayers
     * @param array<string, array<string, int>> $layerViolations
     */
    private function connectEdges(Graph $graph, array $nodes, ConfigurationGraphViz $outputConfig, array $layersDependOnLayers, array $layerViolations) : void
    {
        foreach ($layersDependOnLayers as $layer => $layersDependOn) {
            if (\in_array($layer, $outputConfig->hiddenLayers, \true)) {
                continue;
            }
            foreach ($layersDependOn as $layerDependOn => $layerDependOnCount) {
                if (\in_array($layerDependOn, $outputConfig->hiddenLayers, \true)) {
                    continue;
                }
                $edge = new Edge($nodes[$layer], $nodes[$layerDependOn]);
                if ($outputConfig->pointToGroups && $graph->hasGraph($this->getSubgraphName($layerDependOn))) {
                    $edge->setAttribute('lhead', $this->getSubgraphName($layerDependOn));
                }
                $graph->link($edge);
                if (isset($layerViolations[$layer][$layerDependOn])) {
                    $edge->setAttribute('label', (string) $layerViolations[$layer][$layerDependOn]);
                    $edge->setAttribute('color', 'red');
                } else {
                    $edge->setAttribute('label', (string) $layerDependOnCount);
                }
            }
        }
    }
    /**
     * @param Node[] $nodes
     */
    private function addNodesToGraph(Graph $graph, array $nodes, ConfigurationGraphViz $outputConfig) : void
    {
        foreach ($outputConfig->groupsLayerMap as $groupName => $groupLayerNames) {
            $subgraph = Graph::create($this->getSubgraphName($groupName))->setAttribute('label', $groupName);
            $graph->addGraph($subgraph);
            foreach ($groupLayerNames as $groupLayerName) {
                if (\array_key_exists($groupLayerName, $nodes)) {
                    $subgraph->setNode($nodes[$groupLayerName]);
                    $nodes[$groupLayerName]->setAttribute('group', $groupName);
                    unset($nodes[$groupLayerName]);
                }
            }
        }
        foreach ($nodes as $node) {
            $graph->setNode($node);
        }
    }
    /**
     * @throws Exception
     * @throws OutputException
     */
    protected function getTempImage(Graph $graph) : string
    {
        $filename = tempnam(sys_get_temp_dir(), 'deptrac');
        if (\false === $filename) {
            throw OutputException::withMessage('Unable to create temp file for output.');
        }
        $filename .= '.png';
        $graph->export('png', $filename);
        return $filename;
    }
    private function getSubgraphName(string $groupName) : string
    {
        return 'cluster_' . $groupName;
    }
    /**
     * @throws OutputException
     */
    protected abstract function output(Graph $graph, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void;
}
