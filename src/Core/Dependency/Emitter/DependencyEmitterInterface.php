<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
interface DependencyEmitterInterface
{
    public function getName() : string;
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void;
}
