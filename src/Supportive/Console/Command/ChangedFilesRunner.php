<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\CoveredRuleInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Core\Analyser\AnalyserException;
use Deptrac\Deptrac\Core\Analyser\DependencyLayersAnalyser;
use Deptrac\Deptrac\Core\Analyser\LayerForTokenAnalyser;
use Deptrac\Deptrac\Core\Analyser\TokenType;
/**
 * @internal Should only be used by ChangedFilesCommand
 */
final class ChangedFilesRunner
{
    public function __construct(private readonly LayerForTokenAnalyser $layerForTokenAnalyser, private readonly DependencyLayersAnalyser $dependencyLayersAnalyser)
    {
    }
    /**
     * @param list<string> $files
     *
     * @throws CommandRunException
     */
    public function run(array $files, bool $withDependencies, OutputInterface $output) : void
    {
        try {
            $layers = [];
            foreach ($files as $file) {
                $matches = $this->layerForTokenAnalyser->findLayerForToken($file, TokenType::FILE);
                foreach ($matches as $match) {
                    foreach ($match as $layer) {
                        $layers[$layer] = $layer;
                    }
                }
            }
            $output->writeLineFormatted(\implode(';', $layers));
        } catch (AnalyserException $exception) {
            throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($exception);
        }
        if ($withDependencies) {
            try {
                $result = OutputResult::fromAnalysisResult($this->dependencyLayersAnalyser->analyse());
                $layersDependOnLayers = $this->calculateLayerDependencies($result->allRules());
                $layerDependencies = [];
                foreach ($layers as $layer) {
                    $layerDependencies += $layersDependOnLayers[$layer] ?? [];
                }
                do {
                    $size = \count($layerDependencies);
                    $layerDependenciesCopy = $layerDependencies;
                    foreach ($layerDependenciesCopy as $layerDependency) {
                        $layerDependencies += $layersDependOnLayers[$layerDependency] ?? [];
                    }
                } while ($size !== \count($layerDependencies));
                $output->writeLineFormatted(\implode(';', $layerDependencies));
            } catch (AnalyserException $exception) {
                throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($exception);
            }
        }
    }
    /**
     * @param RuleInterface[] $rules
     *
     * @return array<string, array<string, string>>
     */
    private function calculateLayerDependencies(array $rules) : array
    {
        $layersDependOnLayers = [];
        foreach ($rules as $rule) {
            if (!$rule instanceof CoveredRuleInterface) {
                continue;
            }
            $layerA = $rule->getDependerLayer();
            $layerB = $rule->getDependentLayer();
            if (!isset($layersDependOnLayers[$layerB])) {
                $layersDependOnLayers[$layerB] = [];
            }
            if (!\array_key_exists($layerA, $layersDependOnLayers[$layerB])) {
                $layersDependOnLayers[$layerB][$layerA] = $layerA;
            }
        }
        return $layersDependOnLayers;
    }
}
