<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Analyser;

use Deptrac\Deptrac\Contract\Result\Error;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\Warning;
use function spl_object_id;
/**
 * Describes the result of a source code analysis.
 */
final class AnalysisResult
{
    /**
     * @var array<class-string<RuleInterface>, array<int, RuleInterface>> Rule type -> (ruleInstanceHash -> Rule)
     */
    private array $rules = [];
    /**
     * @var list<Warning>
     */
    private array $warnings = [];
    /**
     * @var list<Error>
     */
    private array $errors = [];
    public function addRule(RuleInterface $rule) : void
    {
        $this->rules[$rule::class][spl_object_id($rule)] = $rule;
    }
    public function removeRule(RuleInterface $rule) : void
    {
        unset($this->rules[$rule::class][spl_object_id($rule)]);
    }
    /**
     * @return array<class-string<RuleInterface>, array<int, RuleInterface>>
     */
    public function rules() : array
    {
        return $this->rules;
    }
    public function addWarning(Warning $warning) : void
    {
        $this->warnings[] = $warning;
    }
    /**
     * @return list<Warning>
     */
    public function warnings() : array
    {
        return $this->warnings;
    }
    public function addError(Error $error) : void
    {
        $this->errors[] = $error;
    }
    /**
     * @return list<Error>
     */
    public function errors() : array
    {
        return $this->errors;
    }
}
