<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Dependency;

use Qossmic\Deptrac\Contract\Dependency\DependencyInterface;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
class DependencyList
{
    /** @var array<string, Dependency[]> */
    private array $dependencies = [];
    /** @var array<string, InheritDependency[]> */
    private array $inheritDependencies = [];
    public function addDependency(\Qossmic\Deptrac\Core\Dependency\Dependency $dependency) : self
    {
        $tokenName = $dependency->getDepender()->toString();
        if (!isset($this->dependencies[$tokenName])) {
            $this->dependencies[$tokenName] = [];
        }
        $this->dependencies[$tokenName][] = $dependency;
        return $this;
    }
    public function addInheritDependency(\Qossmic\Deptrac\Core\Dependency\InheritDependency $dependency) : self
    {
        $classLikeName = $dependency->getDepender()->toString();
        if (!isset($this->inheritDependencies[$classLikeName])) {
            $this->inheritDependencies[$classLikeName] = [];
        }
        $this->inheritDependencies[$classLikeName][] = $dependency;
        return $this;
    }
    /**
     * @return Dependency[]
     */
    public function getDependenciesByClass(ClassLikeToken $classLikeName) : array
    {
        return $this->dependencies[$classLikeName->toString()] ?? [];
    }
    /**
     * @return DependencyInterface[]
     */
    public function getDependenciesAndInheritDependencies() : array
    {
        $buffer = [];
        foreach ($this->dependencies as $deps) {
            foreach ($deps as $dependency) {
                $buffer[] = $dependency;
            }
        }
        foreach ($this->inheritDependencies as $deps) {
            foreach ($deps as $dependency) {
                $buffer[] = $dependency;
            }
        }
        return $buffer;
    }
}
