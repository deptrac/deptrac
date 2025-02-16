<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\Error;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Contract\Result\Warning;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Helper\TableSeparator;
use function count;
final class TableOutputFormatter implements OutputFormatterInterface
{
    public static function getName() : string
    {
        return 'table';
    }
    public function finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $groupedRules = [];
        foreach ($result->allOf(Violation::class) as $rule) {
            $groupedRules[$rule->getDependerLayer()][] = $rule;
        }
        if ($outputFormatterInput->reportSkipped) {
            foreach ($result->allOf(SkippedViolation::class) as $rule) {
                $groupedRules[$rule->getDependerLayer()][] = $rule;
            }
        }
        if ($outputFormatterInput->reportUncovered) {
            foreach ($result->allOf(Uncovered::class) as $rule) {
                $groupedRules[$rule->layer][] = $rule;
            }
        }
        $style = $output->getStyle();
        foreach ($groupedRules as $layer => $rules) {
            $rows = [];
            foreach ($rules as $rule) {
                $rows[] = match (\true) {
                    $rule instanceof Uncovered => $this->uncoveredRow($rule, $outputFormatterInput->failOnUncovered),
                    $rule instanceof Violation => $this->violationRow($rule),
                    $rule instanceof SkippedViolation => $this->skippedViolationRow($rule),
                };
            }
            $style->table(['Reason', $layer], $rows);
        }
        if ($result->hasErrors()) {
            $this->printErrors($result, $output);
        }
        if ($result->hasWarnings()) {
            $this->printWarnings($result, $output);
        }
        $this->printSummary($result, $output, $outputFormatterInput->failOnUncovered);
    }
    /**
     * @return array{string, string}
     */
    private function skippedViolationRow(SkippedViolation $rule) : array
    {
        $dependency = $rule->getDependency();
        $message = \sprintf('<info>%s</info> must not depend on <info>%s</info> (%s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $rule->getDependentLayer());
        if (count($dependency->serialize()) > 1) {
            $message .= "\n" . $this->formatMultilinePath($dependency);
        }
        $fileOccurrence = $rule->getDependency()->getContext()->fileOccurrence;
        $message .= \sprintf("\n%s:%d", $fileOccurrence->filepath, $fileOccurrence->line);
        return ['<fg=yellow>Skipped</>', $message];
    }
    /**
     * @return array{string, string}
     */
    private function violationRow(Violation $rule) : array
    {
        $dependency = $rule->getDependency();
        $message = \sprintf('<info>%s</info> must not depend on <info>%s</info>', $dependency->getDepender()->toString(), $dependency->getDependent()->toString());
        $message .= \sprintf("\n%s (%s)", $rule->ruleDescription(), $rule->getDependentLayer());
        if (count($dependency->serialize()) > 1) {
            $message .= "\n" . $this->formatMultilinePath($dependency);
        }
        $fileOccurrence = $rule->getDependency()->getContext()->fileOccurrence;
        $message .= \sprintf("\n%s:%d", $fileOccurrence->filepath, $fileOccurrence->line);
        return [\sprintf('<fg=red>%s</>', $rule->ruleName()), $message];
    }
    private function formatMultilinePath(DependencyInterface $dep) : string
    {
        return \implode(" -> \n", \array_map(static fn(array $dependency): string => \sprintf('%s::%d', $dependency['name'], $dependency['line']), $dep->serialize()));
    }
    private function printSummary(OutputResult $result, OutputInterface $output, bool $reportUncoveredAsError) : void
    {
        $violationCount = count($result->violations());
        $skippedViolationCount = count($result->skippedViolations());
        $uncoveredCount = count($result->uncovered());
        $allowedCount = count($result->allowed());
        $warningsCount = count($result->warnings);
        $errorsCount = count($result->errors);
        $uncoveredFg = $reportUncoveredAsError ? 'red' : 'yellow';
        $style = $output->getStyle();
        $style->newLine();
        $style->definitionList('Report', new TableSeparator(), ['Violations' => \sprintf('<fg=%s>%d</>', $violationCount > 0 ? 'red' : 'default', $violationCount)], ['Skipped violations' => \sprintf('<fg=%s>%d</>', $skippedViolationCount > 0 ? 'yellow' : 'default', $skippedViolationCount)], ['Uncovered' => \sprintf('<fg=%s>%d</>', $uncoveredCount > 0 ? $uncoveredFg : 'default', $uncoveredCount)], ['Allowed' => $allowedCount], ['Warnings' => \sprintf('<fg=%s>%d</>', $warningsCount > 0 ? 'yellow' : 'default', $warningsCount)], ['Errors' => \sprintf('<fg=%s>%d</>', $errorsCount > 0 ? 'red' : 'default', $errorsCount)]);
    }
    /**
     * @return array{string, string}
     */
    private function uncoveredRow(Uncovered $rule, bool $reportAsError) : array
    {
        $dependency = $rule->getDependency();
        $message = \sprintf('<info>%s</info> has uncovered dependency on <info>%s</info>', $dependency->getDepender()->toString(), $dependency->getDependent()->toString());
        if (count($dependency->serialize()) > 1) {
            $message .= "\n" . $this->formatMultilinePath($dependency);
        }
        $fileOccurrence = $rule->getDependency()->getContext()->fileOccurrence;
        $message .= \sprintf("\n%s:%d", $fileOccurrence->filepath, $fileOccurrence->line);
        return [\sprintf('<fg=%s>Uncovered</>', $reportAsError ? 'red' : 'yellow'), $message];
    }
    private function printErrors(OutputResult $result, OutputInterface $output) : void
    {
        $output->getStyle()->table(['<fg=red>Errors</>'], \array_map(static fn(Error $error) => [(string) $error], $result->errors));
    }
    private function printWarnings(OutputResult $result, OutputInterface $output) : void
    {
        $output->getStyle()->table(['<fg=yellow>Warnings</>'], \array_map(static fn(Warning $warning) => [(string) $warning], $result->warnings));
    }
}
