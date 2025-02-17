<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Dependency\Dependency;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
final class FunctionDependencyEmitter implements \Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
{
    public function getName() : string
    {
        return 'FunctionDependencyEmitter';
    }
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void
    {
        foreach ($astMap->getFileReferences() as $astFileReference) {
            foreach ($astFileReference->functionReferences as $astFunctionReference) {
                foreach ($astFunctionReference->dependencies as $dependency) {
                    if (DependencyType::SUPERGLOBAL_VARIABLE === $dependency->context->dependencyType) {
                        continue;
                    }
                    if (DependencyType::UNRESOLVED_FUNCTION_CALL === $dependency->context->dependencyType) {
                        continue;
                    }
                    $dependencyList->addDependency(new Dependency($astFunctionReference->getToken(), $dependency->token, $dependency->context));
                }
            }
        }
    }
}
