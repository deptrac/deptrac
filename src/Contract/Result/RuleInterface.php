<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Result;

use Qossmic\Deptrac\Contract\Dependency\DependencyInterface;
/**
 * @psalm-immutable
 *
 * Represents a dependency
 */
interface RuleInterface
{
    public function getDependency() : DependencyInterface;
}
