<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyListInterface;

class DependencyList implements DependencyListInterface
{
    /** @var array<string, DependencyInterface[]> */
    private array $dependencies = [];

    /** @var array<string, InheritDependency[]> */
    private array $inheritDependencies = [];

    public function addDependency(DependencyInterface $dependency): void
    {
        $tokenName = $dependency->getDepender()->toString();
        if (!isset($this->dependencies[$tokenName])) {
            $this->dependencies[$tokenName] = [];
        }

        $this->dependencies[$tokenName][] = $dependency;
    }

    public function addInheritDependency(InheritDependency $dependency): self
    {
        $tokenName = $dependency->getDepender()->toString();
        if (!isset($this->inheritDependencies[$tokenName])) {
            $this->inheritDependencies[$tokenName] = [];
        }

        $this->inheritDependencies[$tokenName][] = $dependency;

        return $this;
    }

    /**
     * @return DependencyInterface[]
     */
    public function getDependenciesByClass(TokenInterface $classLikeName): array
    {
        return $this->dependencies[$classLikeName->toString()] ?? [];
    }

    /**
     * @return DependencyInterface[]
     */
    public function getDependenciesAndInheritDependencies(): array
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
