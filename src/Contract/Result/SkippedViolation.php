<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
/**
 * @psalm-immutable
 *
 * Represents a Violation that is being skipped by the baseline file
 */
final class SkippedViolation implements \Deptrac\Deptrac\Contract\Result\CoveredRuleInterface
{
    public function __construct(private readonly DependencyInterface $dependency, private readonly string $dependerLayer, private readonly string $dependentLayer)
    {
    }
    public function getDependency() : DependencyInterface
    {
        return $this->dependency;
    }
    public function getDependerLayer() : string
    {
        return $this->dependerLayer;
    }
    public function getDependentLayer() : string
    {
        return $this->dependentLayer;
    }
}
