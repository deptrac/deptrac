<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Dependency\Emitter;

use Qossmic\Deptrac\Contract\Ast\DependencyType;
use Qossmic\Deptrac\Core\Ast\AstMap\AstMap;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Qossmic\Deptrac\Core\Ast\AstMap\File\FileReference;
use Qossmic\Deptrac\Core\Ast\AstMap\Function\FunctionReference;
use Qossmic\Deptrac\Core\Ast\AstMap\Function\FunctionToken;
use Qossmic\Deptrac\Core\Dependency\Dependency;
use Qossmic\Deptrac\Core\Dependency\DependencyList;
final class FunctionCallDependencyEmitter implements \Qossmic\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
{
    public function getName() : string
    {
        return 'FunctionCallDependencyEmitter';
    }
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void
    {
        $this->createDependenciesForReferences($astMap->getClassLikeReferences(), $astMap, $dependencyList);
        $this->createDependenciesForReferences($astMap->getFunctionReferences(), $astMap, $dependencyList);
        $this->createDependenciesForReferences($astMap->getFileReferences(), $astMap, $dependencyList);
    }
    /**
     * @param array<FunctionReference|ClassLikeReference|FileReference> $references
     */
    private function createDependenciesForReferences(array $references, AstMap $astMap, DependencyList $dependencyList) : void
    {
        foreach ($references as $reference) {
            foreach ($reference->dependencies as $dependency) {
                if (DependencyType::UNRESOLVED_FUNCTION_CALL !== $dependency->context->dependencyType) {
                    continue;
                }
                $token = $dependency->token;
                \assert($token instanceof FunctionToken);
                if (null === $astMap->getFunctionReferenceForToken($token)) {
                    continue;
                }
                $dependencyList->addDependency(new Dependency($reference->getToken(), $dependency->token, $dependency->context));
            }
        }
    }
}
