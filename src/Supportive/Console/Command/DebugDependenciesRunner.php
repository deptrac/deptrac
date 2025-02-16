<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Core\Analyser\AnalyserException;
use Deptrac\Deptrac\Core\Analyser\LayerDependenciesAnalyser;
/**
 * @internal Should only be used by DebugDependenciesCommand
 */
final class DebugDependenciesRunner
{
    public function __construct(private readonly LayerDependenciesAnalyser $analyser)
    {
    }
    /**
     * @throws CommandRunException
     */
    public function run(OutputInterface $output, string $layer, ?string $target) : void
    {
        try {
            $dependencies = $this->analyser->getDependencies($layer, $target);
            foreach ($dependencies as $targetLayer => $violations) {
                $output->getStyle()->table([$targetLayer], \array_map(fn(Uncovered $violation): array => $this->formatRow($violation), $violations));
            }
        } catch (AnalyserException $e) {
            throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($e);
        }
    }
    /**
     * @return array<string>
     */
    private function formatRow(Uncovered $rule) : array
    {
        $dependency = $rule->getDependency();
        $message = \sprintf('<info>%s</info> depends on <info>%s</info> (%s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $rule->layer);
        $fileOccurrence = $dependency->getContext()->fileOccurrence;
        $message .= \sprintf("\n%s:%d", $fileOccurrence->filepath, $fileOccurrence->line);
        return [$message];
    }
}
