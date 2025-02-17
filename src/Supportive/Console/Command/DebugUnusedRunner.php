<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Core\Analyser\AnalyserException;
use Deptrac\Deptrac\Core\Analyser\RulesetUsageAnalyser;
/**
 * @internal Should only be used by DebugUnusedCommand
 */
final class DebugUnusedRunner
{
    public function __construct(private readonly RulesetUsageAnalyser $analyser)
    {
    }
    /**
     * @throws CommandRunException
     */
    public function run(OutputInterface $output, int $limit) : void
    {
        try {
            $rulesetUsages = $this->analyser->analyse();
            $outputTable = $this->prepareOutputTable($rulesetUsages, $limit);
            $output->getStyle()->table(['Unused'], $outputTable);
        } catch (AnalyserException $e) {
            throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($e);
        }
    }
    /**
     * @param array<string, array<string, int>> $layerNames
     *
     * @return array<array{string}>
     */
    private function prepareOutputTable(array $layerNames, int $limit) : array
    {
        $rows = [];
        foreach ($layerNames as $dependerLayerName => $dependentLayerNames) {
            foreach ($dependentLayerNames as $dependentLayerName => $numberOfDependencies) {
                if ($numberOfDependencies <= $limit) {
                    if (0 === $numberOfDependencies) {
                        $rows[] = ["<info>{$dependerLayerName}</info> layer is not dependent on <info>{$dependentLayerName}</info>"];
                    } else {
                        $rows[] = ["<info>{$dependerLayerName}</info> layer is dependent <info>{$dependentLayerName}</info> layer {$numberOfDependencies} times"];
                    }
                }
            }
        }
        return $rows;
    }
}
