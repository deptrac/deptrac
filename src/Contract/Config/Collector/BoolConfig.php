<?php

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorConfig;
use Deptrac\Deptrac\Contract\Config\CollectorType;
final class BoolConfig extends CollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_BOOL;
    /** @var array<CollectorConfig> */
    private array $mustNot = [];
    /** @var array<CollectorConfig> */
    private array $must = [];
    private function __construct()
    {
    }
    /**
     * @param array<CollectorConfig> $must
     * @param array<CollectorConfig> $mostNot
     */
    public static function create(array $must = [], array $mostNot = []) : self
    {
        return (new self())->must(...$must)->mustNot(...$mostNot);
    }
    public function mustNot(CollectorConfig ...$collectorConfigs) : self
    {
        foreach ($collectorConfigs as $collectorConfig) {
            $this->mustNot[] = $collectorConfig;
        }
        return $this;
    }
    public function must(CollectorConfig ...$collectorConfigs) : self
    {
        foreach ($collectorConfigs as $collectorConfig) {
            $this->must[] = $collectorConfig;
        }
        return $this;
    }
    /** @return array{
     *     must: array<array-key, array{private: bool, type: string}>|mixed,
     *     must_not: array<array-key, array{private: bool, type: string}>|mixed,
     *     private: bool,
     *     type: string}
     */
    public function toArray() : array
    {
        return ['must_not' => \array_map(static fn(CollectorConfig $v) => $v->toArray(), $this->mustNot), 'must' => \array_map(static fn(CollectorConfig $v) => $v->toArray(), $this->must), 'private' => $this->private, 'type' => $this->collectorType->value];
    }
}
