<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Dependency\DependencyEmitterInterface;
use Deptrac\Deptrac\Contract\Dependency\PostEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PostFlattenEvent;
use Deptrac\Deptrac\Contract\Dependency\PreEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PreFlattenEvent;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class DependencyResolver
{
    /**
     * @param array{types: array<string>} $config
     */
    public function __construct(
        private readonly array $config,
        private readonly ContainerInterface $emitterLocator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    /**
     * @throws InvalidEmitterConfigurationException
     */
    public function resolve(AstMap $astMap): DependencyList
    {
        $result = new DependencyList();

        foreach ($this->config['types'] as $type) {
            try {
                $emitter = $this->emitterLocator->get($type);
            } catch (ContainerExceptionInterface) {
                throw InvalidEmitterConfigurationException::couldNotLocate($type);
            }
            if (!$emitter instanceof DependencyEmitterInterface) {
                throw InvalidEmitterConfigurationException::isNotEmitter($type, $emitter);
            }

            $this->eventDispatcher->dispatch(new PreEmitEvent($emitter->getName()));
            $emitter->applyDependencies($astMap, $result);
            $this->eventDispatcher->dispatch(new PostEmitEvent());
        }

        $this->eventDispatcher->dispatch(new PreFlattenEvent());
        self::flattenDependencies($astMap, $result);
        $this->eventDispatcher->dispatch(new PostFlattenEvent());

        return $result;
    }

    public static function flattenDependencies(AstMap $astMap, DependencyList $dependencyList): void
    {
        foreach ($astMap->getClassLikeReferences() as $classReference) {
            $classLikeName = $classReference->getToken();
            foreach ($astMap->getClassInherits($classLikeName) as $inherit) {
                foreach ($dependencyList->getDependenciesByClass($inherit->classLikeName) as $dep) {
                    $dependencyList->addInheritDependency(
                        new InheritDependency(
                            $classLikeName, $dep->getDependent(), $dep, $inherit
                        )
                    );
                }
            }
        }
    }
}
