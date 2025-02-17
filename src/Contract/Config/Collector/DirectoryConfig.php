<?php

namespace Qossmic\Deptrac\Contract\Config\Collector;

use Qossmic\Deptrac\Contract\Config\CollectorType;
use Qossmic\Deptrac\Contract\Config\ConfigurableCollectorConfig;
final class DirectoryConfig extends ConfigurableCollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_DIRECTORY;
}
