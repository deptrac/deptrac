<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\ClassLikeConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $config
        ->paths('./src')
        ->excludeFiles('#.*test.*#')
        ->layers(
            $controller = Layer::withName('Controller')->collectors(
                ClassLikeConfig::create('.*Controller.*'),
            ),
            $repository = Layer::withName('Repository')->collectors(
                ClassLikeConfig::create('.*Repository.*'),
            ),
            $service = Layer::withName('Service')->collectors(
                ClassLikeConfig::create('.*Service.*'),
            ),
        )
        ->rulesets(
            Ruleset::forLayer($controller)->accesses($service),
            Ruleset::forLayer($service)->accesses($repository),
            Ruleset::forLayer($repository),
        )
    ;
};
