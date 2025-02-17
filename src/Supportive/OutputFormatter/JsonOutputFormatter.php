<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use Exception;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use function json_encode;
use function json_last_error;
use function sprintf;
use const JSON_PRETTY_PRINT;
/**
 * @internal
 */
final class JsonOutputFormatter implements OutputFormatterInterface
{
    public static function getName() : string
    {
        return 'json';
    }
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function finish(OutputResult $result, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $jsonArray = [];
        $violations = [];
        if ($outputFormatterInput->reportSkipped) {
            foreach ($result->allOf(SkippedViolation::class) as $rule) {
                $this->addSkipped($violations, $rule);
            }
        }
        if ($outputFormatterInput->reportUncovered) {
            foreach ($result->allOf(Uncovered::class) as $rule) {
                $this->addUncovered($violations, $rule);
            }
        }
        foreach ($result->allOf(Violation::class) as $rule) {
            $this->addFailure($violations, $rule);
        }
        $jsonArray['Report'] = ['Violations' => \count($result->violations()), 'Skipped violations' => \count($result->skippedViolations()), 'Uncovered' => \count($result->uncovered()), 'Allowed' => \count($result->allowed()), 'Warnings' => \count($result->warnings), 'Errors' => \count($result->errors)];
        foreach ($violations as &$value) {
            $value['violations'] = \count($value['messages']);
        }
        $jsonArray['files'] = $violations;
        $json = json_encode($jsonArray, JSON_PRETTY_PRINT);
        if (\false === $json) {
            throw new Exception(sprintf('Unable to render json output. %s', $this->jsonLastError()));
        }
        $dumpJsonPath = $outputFormatterInput->outputPath;
        if (null !== $dumpJsonPath) {
            \file_put_contents($dumpJsonPath, $json);
            $output->writeLineFormatted('<info>JSON Report dumped to ' . \realpath($dumpJsonPath) . '</info>');
            return;
        }
        $output->writeRaw($json);
    }
    /**
     * @param array<string, array{messages: array<int, array{message: string, line: int, type: string}>}> $violationsArray
     */
    private function addFailure(array &$violationsArray, Violation $violation) : void
    {
        $className = $violation->getDependency()->getContext()->fileOccurrence->filepath;
        $violationsArray[$className]['messages'][] = ['message' => $this->getFailureMessage($violation), 'line' => $violation->getDependency()->getContext()->fileOccurrence->line, 'type' => 'error'];
    }
    private function getFailureMessage(Violation $violation) : string
    {
        $dependency = $violation->getDependency();
        return sprintf('%s must not depend on %s (%s on %s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $violation->getDependerLayer(), $violation->getDependentLayer());
    }
    /**
     * @param array<string, array{messages: array<int, array{message: string, line: int, type: string}>}> $violationsArray
     */
    private function addSkipped(array &$violationsArray, SkippedViolation $violation) : void
    {
        $className = $violation->getDependency()->getContext()->fileOccurrence->filepath;
        $violationsArray[$className]['messages'][] = ['message' => $this->getWarningMessage($violation), 'line' => $violation->getDependency()->getContext()->fileOccurrence->line, 'type' => 'warning'];
    }
    private function getWarningMessage(SkippedViolation $violation) : string
    {
        $dependency = $violation->getDependency();
        return sprintf('%s should not depend on %s (%s on %s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $violation->getDependerLayer(), $violation->getDependentLayer());
    }
    /**
     * @param array<string, array{messages: array<int, array{message: string, line: int, type: string}>}> $violationsArray
     */
    private function addUncovered(array &$violationsArray, Uncovered $violation) : void
    {
        $className = $violation->getDependency()->getContext()->fileOccurrence->filepath;
        $violationsArray[$className]['messages'][] = ['message' => $this->getUncoveredMessage($violation), 'line' => $violation->getDependency()->getContext()->fileOccurrence->line, 'type' => 'warning'];
    }
    private function getUncoveredMessage(Uncovered $violation) : string
    {
        $dependency = $violation->getDependency();
        return sprintf('%s has uncovered dependency on %s (%s)', $dependency->getDepender()->toString(), $dependency->getDependent()->toString(), $violation->layer);
    }
    private function jsonLastError() : string
    {
        return match (json_last_error()) {
            \JSON_ERROR_NONE => 'No errors',
            \JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            \JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            \JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            \JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            \JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            default => 'Unknown error',
        };
    }
}
