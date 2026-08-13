<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Config\EmitterType;
use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyListInterface;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use SplObjectStorage;

final class ClassDependencyEmitter implements DependencyEmitterInterface
{
    private readonly bool $atMethodGranularity;

    /**
     * @param array{types?: array<string>} $config
     */
    public function __construct(array $config = [])
    {
        $this->atMethodGranularity = in_array(EmitterType::METHOD_TOKEN->value, $config['types'] ?? [], true);
    }

    public function getName(): string
    {
        return 'ClassDependencyEmitter';
    }

    public function applyDependencies(AstMapInterface $astMap, DependencyListInterface $dependencyList): void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            $classLikeName = $classReference->getToken();

            // when the method emitter is active it owns the method-declared dependencies
            $methodDependencies = new SplObjectStorage();
            if ($this->atMethodGranularity) {
                foreach ($classReference->methods as $methodReference) {
                    foreach ($methodReference->dependencies as $methodDependency) {
                        $methodDependencies->attach($methodDependency);
                    }
                }
            }

            foreach ($classReference->dependencies as $dependency) {
                if ($methodDependencies->contains($dependency)) {
                    continue;
                }
                if (DependencyType::SUPERGLOBAL_VARIABLE === $dependency->context->dependencyType) {
                    continue;
                }
                if (DependencyType::UNRESOLVED_FUNCTION_CALL === $dependency->context->dependencyType) {
                    continue;
                }
                // intra-class dispatch is emitted at method granularity only
                if (DependencyType::METHOD_CALL === $dependency->context->dependencyType) {
                    continue;
                }

                $dependencyList->addDependency(
                    new Dependency(
                        $classLikeName,
                        $dependency->token,
                        $dependency->context,
                    )
                );
            }

            foreach ($astMap->getClassInherits($classLikeName) as $inherit) {
                $dependencyList->addDependency(
                    new Dependency(
                        $classLikeName,
                        $inherit->classLikeName,
                        new DependencyContext($inherit->fileOccurrence, DependencyType::INHERIT),
                    )
                );
            }
        }
    }
}
