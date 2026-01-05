<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

final class Layer
{
    /** @var array<CollectorConfig> */
    private array $collectors = [];

    /** @param  array<CollectorConfig> $collectorConfig */
    public function __construct(public string $name, array $collectorConfig = [])
    {
        $this->collectors(...$collectorConfig);
    }

    public static function withName(string $name): self
    {
        return new self($name);
    }

    public function collectors(CollectorConfig ...$collectorConfigs): self
    {
        foreach ($collectorConfigs as $collectorConfig) {
            $this->collectors[] = $collectorConfig;
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'collectors' => array_map(static fn (CollectorConfig $config) => $config->toArray(), $this->collectors),
        ];
    }
}
