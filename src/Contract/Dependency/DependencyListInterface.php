<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Dependency;

interface DependencyListInterface
{
    public function addDependency(DependencyInterface $dependency): void;
}
