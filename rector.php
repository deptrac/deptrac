<?php

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;

return RectorConfig::configure()
    ->withPaths([__DIR__])
    ->withCache('./.cache/rector/')
    ->withSkip([
        './phpstan-baseline.php',
        './vendor',
        './tools',
        './docs',
        './tests/*/Fixtures/*',

         NewInInitializerRector::class,
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        // typeDeclarations: true,
        // privatization: true,
    )
    ->withAttributesSets(
        // symfony: true,
        // phpunit: true,
    )
    // ->withImportNames(importShortClasses: false)
    ->withParallel()
;
