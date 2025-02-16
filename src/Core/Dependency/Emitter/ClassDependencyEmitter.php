<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Dependency\Dependency;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
final class ClassDependencyEmitter implements \Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
{
    public function getName() : string
    {
        return 'ClassDependencyEmitter';
    }
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            $classLikeName = $classReference->getToken();
            foreach ($classReference->dependencies as $dependency) {
                if (DependencyType::SUPERGLOBAL_VARIABLE === $dependency->context->dependencyType) {
                    continue;
                }
                if (DependencyType::UNRESOLVED_FUNCTION_CALL === $dependency->context->dependencyType) {
                    continue;
                }
                $dependencyList->addDependency(new Dependency($classLikeName, $dependency->token, $dependency->context));
            }
            foreach ($astMap->getClassInherits($classLikeName) as $inherit) {
                $dependencyList->addDependency(new Dependency($classLikeName, $inherit->classLikeName, new DependencyContext($inherit->fileOccurrence, DependencyType::INHERIT)));
            }
        }
    }
}
