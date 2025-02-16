<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config;

final class Layer
{
    /** @var array<CollectorConfig> */
    private array $collectors = [];
    public string $name;
    /** @param  array<CollectorConfig> $collectorConfig */
    public function __construct(string $name, array $collectorConfig = [])
    {
        $this->name = $name;
        $this->collectors(...$collectorConfig);
    }
    public static function withName(string $name) : self
    {
        return new self($name);
    }
    public function collectors(\Deptrac\Deptrac\Contract\Config\CollectorConfig ...$collectorConfigs) : self
    {
        foreach ($collectorConfigs as $collectorConfig) {
            $this->collectors[] = $collectorConfig;
        }
        return $this;
    }
    /** @return array<string, mixed> */
    public function toArray() : array
    {
        return ['name' => $this->name, 'collectors' => \array_map(static fn(\Deptrac\Deptrac\Contract\Config\CollectorConfig $config) => $config->toArray(), $this->collectors)];
    }
}
