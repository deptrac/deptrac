<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Config;

final class Ruleset
{
    public \Qossmic\Deptrac\Contract\Config\Layer $layerConfig;
    /** @var array<Layer> */
    private array $accessableLayers = [];
    /** @param  array<Layer> $layerConfigs */
    public function __construct(\Qossmic\Deptrac\Contract\Config\Layer $layerConfig, array $layerConfigs)
    {
        $this->layerConfig = $layerConfig;
        $this->accesses(...$layerConfigs);
    }
    public static function forLayer(\Qossmic\Deptrac\Contract\Config\Layer $layerConfig) : self
    {
        return new self($layerConfig, []);
    }
    public function accesses(\Qossmic\Deptrac\Contract\Config\Layer ...$layerConfigs) : self
    {
        foreach ($layerConfigs as $layerConfig) {
            $this->accessableLayers[] = $layerConfig;
        }
        return $this;
    }
    /** @return non-empty-array<array-key, string> */
    public function toArray() : array
    {
        $data = \array_map(static fn(\Qossmic\Deptrac\Contract\Config\Layer $layerConfig) => $layerConfig->name, $this->accessableLayers);
        return $data + ['name' => $this->layerConfig->name];
    }
}
