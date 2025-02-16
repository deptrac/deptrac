<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
class InheritanceFlattener
{
    public function flattenDependencies(AstMap $astMap, \Deptrac\Deptrac\Core\Dependency\DependencyList $dependencyList) : void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            $classLikeName = $classReference->getToken();
            foreach ($astMap->getClassInherits($classLikeName) as $inherit) {
                foreach ($dependencyList->getDependenciesByClass($inherit->classLikeName) as $dep) {
                    $dependencyList->addInheritDependency(new \Deptrac\Deptrac\Core\Dependency\InheritDependency($classLikeName, $dep->getDependent(), $dep, $inherit));
                }
            }
        }
    }
}
