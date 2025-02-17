<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config\Formatter;

use Deptrac\Deptrac\Contract\Config\Layer;
final class GraphvizConfig implements \Deptrac\Deptrac\Contract\Config\Formatter\FormatterConfigInterface
{
    private string $name = 'graphviz';
    private bool $pointsToGroup = \false;
    /** @var Layer[] */
    private array $hiddenLayers = [];
    /** @var array<string, Layer[]> */
    private array $groups = [];
    private function __construct()
    {
    }
    public static function create() : self
    {
        return new self();
    }
    public function pointsToGroup(bool $pointsToGroup = \true) : self
    {
        $this->pointsToGroup = $pointsToGroup;
        return $this;
    }
    public function hiddenLayers(Layer ...$LayerConfigs) : self
    {
        foreach ($LayerConfigs as $layerConfig) {
            $this->hiddenLayers[] = $layerConfig;
        }
        return $this;
    }
    public function groups(string $name, Layer ...$layerConfigs) : self
    {
        foreach ($layerConfigs as $layerConfig) {
            $this->groups[$name][] = $layerConfig;
        }
        return $this;
    }
    public function toArray() : array
    {
        $output = [];
        if ([] !== $this->hiddenLayers) {
            $output['hidden_layers'] = \array_map(static fn(Layer $config) => $config->name, $this->hiddenLayers);
        }
        if ([] !== $this->groups) {
            $output['groups'] = \array_map(static fn(array $configs) => \array_map(static fn(Layer $layer) => $layer->name, $configs), $this->groups);
        }
        $output['point_to_groups'] = $this->pointsToGroup;
        return $output;
    }
    public function getName() : string
    {
        return $this->name;
    }
}
