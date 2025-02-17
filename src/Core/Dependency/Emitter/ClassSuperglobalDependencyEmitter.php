<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Dependency\Dependency;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
final class ClassSuperglobalDependencyEmitter implements \Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
{
    public function getName() : string
    {
        return 'ClassSuperglobalDependencyEmitter';
    }
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            foreach ($classReference->dependencies as $dependency) {
                if (DependencyType::SUPERGLOBAL_VARIABLE !== $dependency->context->dependencyType) {
                    continue;
                }
                $dependencyList->addDependency(new Dependency($classReference->getToken(), $dependency->token, $dependency->context));
            }
        }
    }
}
