<?php

namespace Qossmic\Deptrac\Contract\Config\Collector;

use Qossmic\Deptrac\Contract\Config\CollectorType;
use Qossmic\Deptrac\Contract\Config\ConfigurableCollectorConfig;
final class FunctionNameConfig extends ConfigurableCollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_FUNCTION_NAME;
}
