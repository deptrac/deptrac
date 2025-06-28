<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\OutputFormatter;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\FormatterConfiguration;

final class MermaidJSOutputFormatter implements OutputFormatterInterface
{
    /** @var array{direction: string, groups: array<string, string[]>, default_node_options: array<string, string>} */
    private array $config;
    private const GRAPH_TYPE = 'flowchart %s;';

    private const GRAPH_END = '  end;';
    private const SUBGRAPH = '  subgraph %sGroup;';
    private const LAYER = '    %s;';
    private const GRAPH_NODE_FORMAT = '    %s -->|%d| %s;';
    private const VIOLATION_STYLE_FORMAT = '    linkStyle %d stroke:red,stroke-width:4px;';

    public function __construct(FormatterConfiguration $config)
    {
        /** @var array{direction: string, groups: array<string, string[]>, default_node_options: array<string, string>}  $extractedConfig */
        $extractedConfig = $config->getConfigFor('mermaidjs');
        $this->config = $extractedConfig;
    }

    public static function getName(): string
    {
        return 'mermaidjs';
    }

    public function finish(
        OutputResult $result,
        OutputInterface $output,
        OutputFormatterInput $outputFormatterInput,
    ): void {
        $graph = $this->parseResults($result);
        $violations = $result->violations();
        $buffer = '';

        $buffer .= sprintf(self::GRAPH_TYPE.PHP_EOL, $this->config['direction']);

        foreach ($this->config['groups'] as $subGraphName => $layers) {
            $buffer .= sprintf(self::SUBGRAPH.PHP_EOL, $subGraphName);

            foreach ($layers as $layer) {
                $buffer .= sprintf(self::LAYER.PHP_EOL, $layer);
            }

            $buffer .= self::GRAPH_END.PHP_EOL;
        }

        $linkCount = 0;
        $violationsLinks = [];
        $violationGraphLinks = [];

        foreach ($violations as $violation) {
            if (!isset($violationsLinks[$violation->getDependerLayer()][$violation->getDependentLayer()])) {
                $violationsLinks[$violation->getDependerLayer()][$violation->getDependentLayer()] = 1;
            } else {
                ++$violationsLinks[$violation->getDependerLayer()][$violation->getDependentLayer()];
            }
        }

        $compiledNodeOptions = $this->compileDefaultNodeOptions();

        foreach ($violationsLinks as $dependerLayer => $layers) {
            foreach ($layers as $dependentLayer => $count) {
                $renderedDependerNode = self::renderNode($dependerLayer, $compiledNodeOptions);
                $renderedDependentNode = self::renderNode($dependentLayer, $compiledNodeOptions);
                $buffer .= sprintf(self::GRAPH_NODE_FORMAT.PHP_EOL, $renderedDependerNode, $count, $renderedDependentNode);
                $violationGraphLinks[] = $linkCount;
                ++$linkCount;
            }
        }

        foreach ($graph as $dependerLayer => $layers) {
            foreach ($layers as $dependentLayer => $count) {
                if (!isset($violationsLinks[$dependerLayer][$dependentLayer])) {
                    $renderedDependerNode = self::renderNode($dependerLayer, $compiledNodeOptions);
                    $renderedDependentNode = self::renderNode($dependentLayer, $compiledNodeOptions);
                    $buffer .= sprintf(self::GRAPH_NODE_FORMAT.PHP_EOL, $renderedDependerNode, $count, $renderedDependentNode);
                }
            }
        }

        foreach ($violationGraphLinks as $linkNumber) {
            $buffer .= sprintf(self::VIOLATION_STYLE_FORMAT.PHP_EOL, $linkNumber);
        }

        if (null !== $outputFormatterInput->outputPath) {
            file_put_contents($outputFormatterInput->outputPath, $buffer);
        } else {
            $output->writeRaw($buffer);
        }
    }

    /**
     * @return array<string, array<string, int<1, max>>>
     */
    protected function parseResults(OutputResult $result): array
    {
        $graph = [];

        foreach ($result->allowed() as $rule) {
            if (!isset($graph[$rule->getDependerLayer()][$rule->getDependentLayer()])) {
                $graph[$rule->getDependerLayer()][$rule->getDependentLayer()] = 1;
            } else {
                ++$graph[$rule->getDependerLayer()][$rule->getDependentLayer()];
            }
        }

        return $graph;
    }

    protected function compileDefaultNodeOptions(): string
    {
        return implode(
            ', ',
            array_map(
                static fn ($key, $value): string => sprintf('%s: %s', $key, $value),
                array_keys($this->config['default_node_options']),
                $this->config['default_node_options'],
            ),
        );
    }

    protected static function renderNode(string $nodeId, string $compiledNodeOptions): string
    {
        return '' === $compiledNodeOptions ? $nodeId : sprintf('%s@{%s}', $nodeId, $compiledNodeOptions);
    }
}
