<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Dependency\Emitter;

use Qossmic\Deptrac\Contract\Ast\DependencyContext;
use Qossmic\Deptrac\Contract\Ast\DependencyType;
use Qossmic\Deptrac\Core\Ast\AstMap\AstMap;
use Qossmic\Deptrac\Core\Dependency\Dependency;
use Qossmic\Deptrac\Core\Dependency\DependencyList;
final class ClassDependencyEmitter implements \Qossmic\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
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
