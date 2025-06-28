<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

final class Ruleset
{
    public Layer $layerConfig;

    /** @var array<Layer> */
    private array $accessableLayers = [];

    /** @param  array<Layer> $layerConfigs */
    public function __construct(Layer $layerConfig, array $layerConfigs)
    {
        $this->layerConfig = $layerConfig;
        $this->accesses(...$layerConfigs);
    }

    public static function forLayer(Layer $layerConfig): self
    {
        return new self($layerConfig, []);
    }

    public function accesses(Layer ...$layerConfigs): self
    {
        foreach ($layerConfigs as $layerConfig) {
            $this->accessableLayers[] = $layerConfig;
        }

        return $this;
    }

    /** @return non-empty-array<array-key, string> */
    public function toArray(): array
    {
        $data = array_map(static fn (Layer $layerConfig) => $layerConfig->name, $this->accessableLayers);

        return $data + ['name' => $this->layerConfig->name];
    }
}
