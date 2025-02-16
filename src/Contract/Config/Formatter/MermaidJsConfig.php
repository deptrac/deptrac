<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config\Formatter;

use Deptrac\Deptrac\Contract\Config\Layer;
final class MermaidJsConfig implements \Deptrac\Deptrac\Contract\Config\Formatter\FormatterConfigInterface
{
    private string $name = 'mermaidjs';
    private string $direction = 'TD';
    /** @var array<string, Layer[]> */
    private array $groups = [];
    public static function create() : self
    {
        return new self();
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function direction(string $direction) : self
    {
        $this->direction = $direction;
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
        if ([] !== $this->groups) {
            $output['groups'] = \array_map(static fn(array $configs) => \array_map(static fn(Layer $layer) => $layer->name, $configs), $this->groups);
        }
        $output['direction'] = $this->direction;
        return $output;
    }
}
