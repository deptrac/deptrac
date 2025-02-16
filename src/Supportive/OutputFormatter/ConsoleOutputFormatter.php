<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\Contract\Ast\FileOccurrence;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Violation;
use function count;
/**
 * @internal
 */
final class ConsoleOutputFormatter implements OutputFormatterInterface
{
    public static function getName() : string
    {
        return 'console';
    }
    public function finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        foreach ($result->allOf(Violation::class) as $rule) {
            $this->printViolation($rule, $output);
        }
        if ($outputFormatterInput->reportSkipped) {
            foreach ($result->allOf(SkippedViolation::class) as $rule) {
                $this->printViolation($rule, $output);
            }
        }
        if ($outputFormatterInput->reportUncovered) {
            $this->printUncovered($result, $output);
        }
        if ($result->hasErrors()) {
            $this->printErrors($result, $output);
        }
        if ($result->hasWarnings()) {
            $this->printWarnings($result, $output);
        }
        $this->printSummary($result, $output);
    }
    private function printViolation(Violation|SkippedViolation $rule, OutputInterface $output) : void
    {
        $dependency = $rule->getDependency();
        $output->writeLineFormatted(\sprintf('%s<info>%s</info> must not depend on <info>%s</info> (%s on %s)', $rule instanceof SkippedViolation ? '[SKIPPED] ' : '', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $rule->getDependerLayer(), $rule->getDependentLayer()));
        $this->printFileOccurrence($output, $dependency->getContext()->fileOccurrence);
        if (count($dependency->serialize()) > 1) {
            $this->printMultilinePath($output, $dependency);
        }
    }
    private function printMultilinePath(OutputInterface $output, DependencyInterface $dep) : void
    {
        $buffer = \implode(" -> \n", \array_map(static fn(array $dependency): string => \sprintf("\t%s:%d", $dependency['name'], $dependency['line']), $dep->serialize()));
        $output->writeLineFormatted($buffer);
    }
    private function printSummary(OutputResult $result, OutputInterface $output) : void
    {
        $violationCount = count($result->violations());
        $skippedViolationCount = count($result->skippedViolations());
        $uncoveredCount = count($result->uncovered());
        $allowedCount = count($result->allowed());
        $warningsCount = count($result->warnings);
        $errorsCount = count($result->errors);
        $output->writeLineFormatted('');
        $output->writeLineFormatted('Report:');
        $output->writeLineFormatted(\sprintf('<fg=%s>Violations: %d</>', $violationCount > 0 ? 'red' : 'default', $violationCount));
        $output->writeLineFormatted(\sprintf('<fg=%s>Skipped violations: %d</>', $skippedViolationCount > 0 ? 'yellow' : 'default', $skippedViolationCount));
        $output->writeLineFormatted(\sprintf('<fg=%s>Uncovered: %d</>', $uncoveredCount > 0 ? 'yellow' : 'default', $uncoveredCount));
        $output->writeLineFormatted(\sprintf('<info>Allowed: %d</info>', $allowedCount));
        $output->writeLineFormatted(\sprintf('<fg=%s>Warnings: %d</>', $warningsCount > 0 ? 'yellow' : 'default', $warningsCount));
        $output->writeLineFormatted(\sprintf('<fg=%s>Errors: %d</>', $errorsCount > 0 ? 'red' : 'default', $errorsCount));
    }
    private function printUncovered(OutputResult $result, OutputInterface $output) : void
    {
        $uncovered = $result->uncovered();
        if ([] === $uncovered) {
            return;
        }
        $output->writeLineFormatted('<comment>Uncovered dependencies:</comment>');
        foreach ($uncovered as $u) {
            $dependency = $u->getDependency();
            $output->writeLineFormatted(\sprintf('<info>%s</info> has uncovered dependency on <info>%s</info> (%s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $u->layer));
            $this->printFileOccurrence($output, $dependency->getContext()->fileOccurrence);
            if (count($dependency->serialize()) > 1) {
                $this->printMultilinePath($output, $dependency);
            }
        }
    }
    private function printFileOccurrence(OutputInterface $output, FileOccurrence $fileOccurrence) : void
    {
        $output->writeLineFormatted($fileOccurrence->filepath . ':' . $fileOccurrence->line);
    }
    private function printErrors(OutputResult $result, OutputInterface $output) : void
    {
        $output->writeLineFormatted('');
        foreach ($result->errors as $error) {
            $output->writeLineFormatted(\sprintf('<fg=red>[ERROR]</> %s', (string) $error));
        }
    }
    private function printWarnings(OutputResult $result, OutputInterface $output) : void
    {
        $output->writeLineFormatted('');
        foreach ($result->warnings as $warning) {
            $output->writeLineFormatted(\sprintf('<fg=yellow>[WARNING]</> %s', (string) $warning));
        }
    }
}
