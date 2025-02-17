<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
/**
 * @psalm-immutable
 *
 * Represents a dependency that is NOT covered by the current configuration.
 */
final class Uncovered implements \Deptrac\Deptrac\Contract\Result\RuleInterface
{
    public function __construct(private readonly DependencyInterface $dependency, public readonly string $layer)
    {
    }
    public function getDependency() : DependencyInterface
    {
        return $this->dependency;
    }
}
