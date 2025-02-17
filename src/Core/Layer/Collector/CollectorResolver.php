<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use DEPTRAC_INTERNAL\Psr\Container\ContainerExceptionInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use function array_key_exists;
use function is_string;
final class CollectorResolver implements \Qossmic\Deptrac\Core\Layer\Collector\CollectorResolverInterface
{
    public function __construct(private readonly \Qossmic\Deptrac\Core\Layer\Collector\CollectorProvider $collectorProvider)
    {
    }
    /**
     * @param array<string, string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    public function resolve(array $config) : \Qossmic\Deptrac\Core\Layer\Collector\Collectable
    {
        if (!array_key_exists('type', $config) || !is_string($config['type'])) {
            throw InvalidCollectorDefinitionException::missingType();
        }
        try {
            $collector = $this->collectorProvider->get($config['type']);
        } catch (ContainerExceptionInterface $containerException) {
            throw InvalidCollectorDefinitionException::unsupportedType($config['type'], $this->collectorProvider->getKnownCollectors(), $containerException);
        }
        return new \Qossmic\Deptrac\Core\Layer\Collector\Collectable($collector, $config);
    }
}
