<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency;

use DEPTRAC_INTERNAL\Psr\Container\ContainerExceptionInterface;
use DEPTRAC_INTERNAL\Psr\Container\ContainerInterface;
use DEPTRAC_INTERNAL\Psr\EventDispatcher\EventDispatcherInterface;
use Deptrac\Deptrac\Contract\Dependency\PostEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PostFlattenEvent;
use Deptrac\Deptrac\Contract\Dependency\PreEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PreFlattenEvent;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface;
class DependencyResolver
{
    /**
     * @param array{types: array<string>} $config
     */
    public function __construct(private readonly array $config, private readonly \Deptrac\Deptrac\Core\Dependency\InheritanceFlattener $inheritanceFlattener, private readonly ContainerInterface $emitterLocator, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }
    /**
     * @throws InvalidEmitterConfigurationException
     */
    public function resolve(AstMap $astMap) : \Deptrac\Deptrac\Core\Dependency\DependencyList
    {
        $result = new \Deptrac\Deptrac\Core\Dependency\DependencyList();
        foreach ($this->config['types'] as $type) {
            try {
                $emitter = $this->emitterLocator->get($type);
            } catch (ContainerExceptionInterface) {
                throw \Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException::couldNotLocate($type);
            }
            if (!$emitter instanceof DependencyEmitterInterface) {
                throw \Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException::isNotEmitter($type, $emitter);
            }
            $this->eventDispatcher->dispatch(new PreEmitEvent($emitter->getName()));
            $emitter->applyDependencies($astMap, $result);
            $this->eventDispatcher->dispatch(new PostEmitEvent());
        }
        $this->eventDispatcher->dispatch(new PreFlattenEvent());
        $this->inheritanceFlattener->flattenDependencies($astMap, $result);
        $this->eventDispatcher->dispatch(new PostFlattenEvent());
        return $result;
    }
}
