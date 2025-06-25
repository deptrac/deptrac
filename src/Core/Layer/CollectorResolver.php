<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Layer;

use Deptrac\Deptrac\Contract\Layer\Collectable;
use Deptrac\Deptrac\Contract\Layer\CollectorResolverInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Psr\Container\ContainerExceptionInterface;

use function array_key_exists;
use function is_string;

final class CollectorResolver implements CollectorResolverInterface
{
    public function __construct(private readonly CollectorProvider $collectorProvider) {}

    /**
     * @param array<string, string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    public function resolve(array $config): Collectable
    {
        if (!array_key_exists('type', $config) || !is_string($config['type'])) {
            throw InvalidCollectorDefinitionException::missingType();
        }

        try {
            $collector = $this->collectorProvider->get($config['type']);
        } catch (ContainerExceptionInterface $containerException) {
            throw InvalidCollectorDefinitionException::unsupportedType($config['type'], $this->collectorProvider->getKnownCollectors(), $containerException);
        }

        return new Collectable($collector, $config);
    }
}
