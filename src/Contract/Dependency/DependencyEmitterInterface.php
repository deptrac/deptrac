<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;

interface DependencyEmitterInterface
{
    public function getName(): string;

    public function applyDependencies(AstMapInterface $astMap, DependencyListInterface $dependencyList): void;
}
