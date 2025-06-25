<?php declare(strict_types = 1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    ->addPathToScan(__DIR__ . '/deptrac', isDev: false)
    ->ignoreErrorsOnExtension('ext-dom', [ErrorType::SHADOW_DEPENDENCY]) // in composer "suggests"
    ->ignoreErrorsOnPath(__DIR__ . '/tests', [ErrorType::UNKNOWN_CLASS, ErrorType::UNKNOWN_FUNCTION]); // keep ability to test invalid symbols
