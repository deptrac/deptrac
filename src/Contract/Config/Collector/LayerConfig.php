<?php

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorType;
use Deptrac\Deptrac\Contract\Config\ConfigurableCollectorConfig;
final class LayerConfig extends ConfigurableCollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_LAYER;
}
