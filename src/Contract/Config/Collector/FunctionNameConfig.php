<?php

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorType;
use Deptrac\Deptrac\Contract\Config\ConfigurableCollectorConfig;
final class FunctionNameConfig extends ConfigurableCollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_FUNCTION_NAME;
}
