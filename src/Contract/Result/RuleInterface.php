<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
/**
 * @psalm-immutable
 *
 * Represents a dependency
 */
interface RuleInterface
{
    public function getDependency() : DependencyInterface;
}
