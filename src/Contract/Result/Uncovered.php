<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Result;

use Qossmic\Deptrac\Contract\Dependency\DependencyInterface;
/**
 * @psalm-immutable
 *
 * Represents a dependency that is NOT covered by the current configuration.
 */
final class Uncovered implements \Qossmic\Deptrac\Contract\Result\RuleInterface
{
    public function __construct(private readonly DependencyInterface $dependency, public readonly string $layer)
    {
    }
    public function getDependency() : DependencyInterface
    {
        return $this->dependency;
    }
}
