<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Analyser\AnalysisResult;
use function count;
/**
 * @psalm-immutable
 *
 * Represents a result ready for output formatting
 */
final class OutputResult
{
    /**
     * @param array<class-string<RuleInterface>, array<int, RuleInterface>> $rules
     * @param list<Error> $errors
     * @param list<Warning> $warnings
     */
    private function __construct(public readonly array $rules, public readonly array $errors, public readonly array $warnings)
    {
    }
    public static function fromAnalysisResult(AnalysisResult $analysisResult) : self
    {
        return new self($analysisResult->rules(), $analysisResult->errors(), $analysisResult->warnings());
    }
    /**
     * @template T of RuleInterface
     *
     * @param class-string<T> $type
     *
     * @return list<T>
     */
    public function allOf(string $type) : array
    {
        return \array_key_exists($type, $this->rules) ? \array_values($this->rules[$type]) : [];
    }
    /**
     * @return list<RuleInterface>
     */
    public function allRules() : array
    {
        $rules = [];
        foreach ($this->rules as $ruleArray) {
            foreach ($ruleArray as $rule) {
                $rules[] = $rule;
            }
        }
        return $rules;
    }
    /**
     * @return list<Violation>
     */
    public function violations() : array
    {
        return $this->allOf(\Deptrac\Deptrac\Contract\Result\Violation::class);
    }
    public function hasViolations() : bool
    {
        return count($this->violations()) > 0;
    }
    /**
     * @return list<SkippedViolation>
     */
    public function skippedViolations() : array
    {
        return $this->allOf(\Deptrac\Deptrac\Contract\Result\SkippedViolation::class);
    }
    /**
     * @return list<Uncovered>
     */
    public function uncovered() : array
    {
        return $this->allOf(\Deptrac\Deptrac\Contract\Result\Uncovered::class);
    }
    public function hasUncovered() : bool
    {
        return count($this->uncovered()) > 0;
    }
    /**
     * @return list<Allowed>
     */
    public function allowed() : array
    {
        return $this->allOf(\Deptrac\Deptrac\Contract\Result\Allowed::class);
    }
    public function hasErrors() : bool
    {
        return count($this->errors) > 0;
    }
    public function hasWarnings() : bool
    {
        return count($this->warnings) > 0;
    }
}
