<?php

namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Violation;
final class GithubActionsOutputFormatter implements OutputFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getName() : string
    {
        return 'github-actions';
    }
    /**
     * {@inheritdoc}
     */
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
            $this->printUncovered($result, $output, $outputFormatterInput->failOnUncovered);
        }
        if ($result->hasErrors()) {
            $this->printErrors($result, $output);
        }
        if ($result->hasWarnings()) {
            $this->printWarnings($result, $output);
        }
    }
    private function determineLogLevel(RuleInterface $rule) : string
    {
        return match ($rule::class) {
            Violation::class => 'error',
            SkippedViolation::class => 'warning',
            default => 'debug',
        };
    }
    private function printUncovered(OutputResult $result, OutputInterface $output, bool $reportAsError) : void
    {
        foreach ($result->uncovered() as $u) {
            $dependency = $u->getDependency();
            $output->writeLineFormatted(\sprintf('::%s file=%s,line=%s::%s has uncovered dependency on %s (%s)', $reportAsError ? 'error' : 'warning', $dependency->getContext()->fileOccurrence->filepath, $dependency->getContext()->fileOccurrence->line, $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $u->layer));
        }
    }
    private function multilinePathMessage(DependencyInterface $dep) : string
    {
        return \implode(' ->%0A', \array_map(static fn(array $dependency): string => \sprintf('%s::%d', $dependency['name'], $dependency['line']), $dep->serialize()));
    }
    private function printErrors(OutputResult $result, OutputInterface $output) : void
    {
        foreach ($result->errors as $error) {
            $output->writeLineFormatted('::error ::' . (string) $error);
        }
    }
    private function printWarnings(OutputResult $result, OutputInterface $output) : void
    {
        foreach ($result->warnings as $warning) {
            $output->writeLineFormatted('::warning ::' . (string) $warning);
        }
    }
    private function printViolation(Violation|SkippedViolation $rule, OutputInterface $output) : void
    {
        $dependency = $rule->getDependency();
        $message = \sprintf('%s%s must not depend on %s (%s on %s)', $rule instanceof SkippedViolation ? '[SKIPPED] ' : '', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $rule->getDependerLayer(), $rule->getDependentLayer());
        if (\count($dependency->serialize()) > 1) {
            $message .= '%0A' . $this->multilinePathMessage($dependency);
        }
        $output->writeLineFormatted(\sprintf('::%s file=%s,line=%s::%s', $this->determineLogLevel($rule), $dependency->getContext()->fileOccurrence->filepath, $dependency->getContext()->fileOccurrence->line, $message));
    }
}
