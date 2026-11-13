<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyListInterface;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use JetBrains\PHPStormStub\PhpStormStubsMap;

use function array_change_key_case;
use function strrpos;
use function strtolower;
use function substr;

/**
 * Emits a dependency for every call to a function that is known: either
 * because it is declared in the analysed code, or because it is a
 * PHP-internal function.
 */
final class FunctionCallDependencyEmitter implements DependencyEmitterInterface
{
    /**
     * @var ?array<string, string>
     */
    private ?array $internalFunctions = null;

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
            foreach ($this->resolveFunctionCalls($reference->dependencies, $astMap) as $dependency) {
                $dependencyList->addDependency(
                    new Dependency(
                        $reference->getToken(), $dependency->token, $dependency->context
                    )
                );
            }
        }
    }

    /**
     * An unqualified call inside a namespace is recorded twice: for the
     * namespaced function and for the global function PHP falls back to if
     * the namespaced one does not exist. Only one of the two is called.
     *
     * @param DependencyToken[] $dependencies
     *
     * @return list<DependencyToken>
     */
    private function resolveFunctionCalls(array $dependencies, AstMapInterface $astMap): array
    {
        $calls = [];
        $namespacedCalls = [];

        foreach ($dependencies as $dependency) {
            if (DependencyType::UNRESOLVED_FUNCTION_CALL !== $dependency->context->dependencyType) {
                continue;
            }

            $token = $dependency->token;
            assert($token instanceof FunctionToken);

            if (null === $astMap->getFunctionReferenceForToken($token) && !$this->isInternalFunction($token)) {
                continue;
            }

            $calls[] = $dependency;

            $namespaceSeparator = strrpos($token->functionName, '\\');
            if (false !== $namespaceSeparator) {
                $line = $dependency->context->fileOccurrence->line;
                $namespacedCalls[$line][substr($token->functionName, $namespaceSeparator + 1)] = $dependency;
            }
        }

        $resolved = [];

        foreach ($calls as $dependency) {
            $token = $dependency->token;
            assert($token instanceof FunctionToken);

            // The namespaced function exists, PHP does not fall back to this one.
            if (isset($namespacedCalls[$dependency->context->fileOccurrence->line][$token->functionName])) {
                continue;
            }

            $resolved[] = $dependency;
        }

        return $resolved;
    }

    private function isInternalFunction(FunctionToken $token): bool
    {
        $this->internalFunctions ??= array_change_key_case(PhpStormStubsMap::FUNCTIONS);

        return isset($this->internalFunctions[strtolower($token->functionName)]);
    }
}
