<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Dependency\Dependency;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
final class FileDependencyEmitter implements \Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
{
    public function getName() : string
    {
        return 'FileDependencyEmitter';
    }
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void
    {
        foreach ($astMap->getFileReferences() as $fileReference) {
            foreach ($fileReference->dependencies as $dependency) {
                if (DependencyType::USE === $dependency->context->dependencyType) {
                    continue;
                }
                if (DependencyType::UNRESOLVED_FUNCTION_CALL === $dependency->context->dependencyType) {
                    continue;
                }
                $dependencyList->addDependency(new Dependency($fileReference->getToken(), $dependency->token, $dependency->context));
            }
        }
    }
}
