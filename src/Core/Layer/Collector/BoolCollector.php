<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
use Qossmic\Deptrac\Contract\Layer\CollectorInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
final class BoolCollector implements CollectorInterface
{
    public function __construct(private readonly \Qossmic\Deptrac\Core\Layer\Collector\CollectorResolverInterface $collectorResolver)
    {
    }
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        $configuration = $this->normalizeConfiguration($config);
        /** @var array{type: string, args: array<string, string>} $v */
        foreach ((array) $configuration['must'] as $v) {
            $collectable = $this->collectorResolver->resolve($v);
            $satisfied = $collectable->collector->satisfy($collectable->attributes, $reference);
            if (!$satisfied) {
                return \false;
            }
        }
        /** @var array{type: string, args: array<string, string>} $v */
        foreach ((array) $configuration['must_not'] as $v) {
            $collectable = $this->collectorResolver->resolve($v);
            $satisfied = $collectable->collector->satisfy($collectable->attributes, $reference);
            if ($satisfied) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param array<string, bool|string|array<string, string>> $configuration
     *
     * @return array<string, bool|string|array<string, string>>
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function normalizeConfiguration(array $configuration) : array
    {
        if (!isset($configuration['must'])) {
            $configuration['must'] = [];
        }
        if (!isset($configuration['must_not'])) {
            $configuration['must_not'] = [];
        }
        if (!$configuration['must'] && !$configuration['must_not']) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('"bool" collector must have a "must" or a "must_not" attribute.');
        }
        return $configuration;
    }
}
