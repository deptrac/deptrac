<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Dependency;

use Deptrac\Deptrac\Contract\Ast\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\TokenInterface;
/**
 * Represents a dependency between 2 tokens (depender and dependent).
 */
interface DependencyInterface
{
    public function getDepender() : TokenInterface;
    public function getDependent() : TokenInterface;
    public function getContext() : DependencyContext;
    /**
     * @return array<array{name:string, line:int}>
     */
    public function serialize() : array;
}
