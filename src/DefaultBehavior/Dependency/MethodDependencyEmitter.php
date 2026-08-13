<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyListInterface;
use Deptrac\Deptrac\Contract\Layer\LayerResolverInterface;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;

/**
 * Emits the dependencies declared inside class methods.
 *
 * A method assigned to at least one layer of its own (via a "scope: method"
 * collector) becomes the depender for its dependencies, overriding its class's
 * layers. Dependencies of all other methods stay attributed to the class token,
 * matching the class-level emitter's behavior.
 */
final class MethodDependencyEmitter implements DependencyEmitterInterface
{
    public function __construct(private readonly LayerResolverInterface $layerResolver) {}

    public function getName(): string
    {
        return 'MethodDependencyEmitter';
    }

    /**
     * @throws \Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException
     * @throws \Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException
     * @throws \Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException
     */
    public function applyDependencies(AstMapInterface $astMap, DependencyListInterface $dependencyList): void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            foreach ($classReference->methods as $methodReference) {
                $isLayered = [] !== $this->layerResolver->getLayersForReference($methodReference);
                $depender = $isLayered ? $methodReference->getToken() : $classReference->getToken();

                foreach ($methodReference->dependencies as $dependency) {
                    if (DependencyType::SUPERGLOBAL_VARIABLE === $dependency->context->dependencyType) {
                        continue;
                    }
                    if (DependencyType::UNRESOLVED_FUNCTION_CALL === $dependency->context->dependencyType) {
                        continue;
                    }

                    $dependencyList->addDependency(
                        new Dependency(
                            $depender,
                            $dependency->token,
                            $dependency->context,
                        )
                    );
                }
            }
        }
    }
}
