<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Analyser\ViolationCreatingInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;

/**
 * @psalm-immutable
 *
 * Represents a dependency that is NOT allowed to exist given the defined rules
 */
final readonly class Violation implements CoveredRuleInterface
{
    public function __construct(
        private DependencyInterface $dependency,
        private string $dependerLayer,
        private string $dependentLayer,
        private ViolationCreatingInterface $violationCreatingRule,
    ) {}

    public function getDependency(): DependencyInterface
    {
        return $this->dependency;
    }

    public function getDependerLayer(): string
    {
        return $this->dependerLayer;
    }

    public function getDependentLayer(): string
    {
        return $this->dependentLayer;
    }

    public function ruleName(): string
    {
        return $this->violationCreatingRule->ruleName();
    }

    public function ruleDescription(): string
    {
        return $this->violationCreatingRule->ruleDescription();
    }
}
