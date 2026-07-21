<?php

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorType;
use Deptrac\Deptrac\Contract\Config\ConfigurableCollectorConfig;
use Deptrac\Deptrac\Contract\Config\Layer;

final class LayerConfig extends ConfigurableCollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_LAYER;

    public static function create(string|Layer $config): self
    {
        /** @var self $layerConfig parent::create() instantiates new static() */
        $layerConfig = parent::create($config instanceof Layer ? $config->name : $config);

        return $layerConfig;
    }
}
