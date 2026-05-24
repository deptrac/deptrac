<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;

/**
 * @psalm-immutable
 *
 * Represents a Violation that is being skipped by the baseline file
 */
final readonly class SkippedViolation implements CoveredRuleInterface
{
    public function __construct(private DependencyInterface $dependency, private string $dependerLayer, private string $dependentLayer) {}

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
}
