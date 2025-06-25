<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyListInterface;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;

final class FunctionCallDependencyEmitter implements DependencyEmitterInterface
{
    public function getName(): string
    {
        return 'FunctionCallDependencyEmitter';
    }

    public function applyDependencies(AstMapInterface $astMap, DependencyListInterface $dependencyList): void
    {
        $this->createDependenciesForReferences($astMap->getClassLikeReferences(), $astMap, $dependencyList);
        $this->createDependenciesForReferences($astMap->getFunctionReferences(), $astMap, $dependencyList);
        $this->createDependenciesForReferences($astMap->getFileReferences(), $astMap, $dependencyList);
    }

    /**
     * @param array<FunctionReference|ClassLikeReference|FileReference> $references
     */
    private function createDependenciesForReferences(array $references, AstMapInterface $astMap, DependencyListInterface $dependencyList): void
    {
        foreach ($references as $reference) {
            foreach ($reference->dependencies as $dependency) {
                if (DependencyType::UNRESOLVED_FUNCTION_CALL !== $dependency->context->dependencyType) {
                    continue;
                }

                $token = $dependency->token;
                assert($token instanceof FunctionToken);
                if (null === $astMap->getFunctionReferenceForToken($token)) {
                    continue;
                }

                $dependencyList->addDependency(
                    new Dependency(
                        $reference->getToken(), $dependency->token, $dependency->context
                    )
                );
            }
        }
    }
}
