<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyListInterface;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;

final class ClassSuperglobalDependencyEmitter implements DependencyEmitterInterface
{
    public function getName(): string
    {
        return 'ClassSuperglobalDependencyEmitter';
    }

    public function applyDependencies(AstMapInterface $astMap, DependencyListInterface $dependencyList): void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            foreach ($classReference->dependencies as $dependency) {
                if (DependencyType::SUPERGLOBAL_VARIABLE !== $dependency->context->dependencyType) {
                    continue;
                }
                $dependencyList->addDependency(
                    new Dependency(
                        $classReference->getToken(),
                        $dependency->token,
                        $dependency->context,
                    )
                );
            }
        }
    }
}
