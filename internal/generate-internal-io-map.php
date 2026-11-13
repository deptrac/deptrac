<?php

declare(strict_types=1);

use Internal\Deptrac\Deptrac\InternalIOMapGenerator;

require __DIR__.'/../vendor/autoload.php';

$target = __DIR__.'/../src/DefaultBehavior/Layer/Helpers/InternalIOMap.php';

if (false === file_put_contents($target, InternalIOMapGenerator::forInstalledStubs()->generate())) {
    fwrite(STDERR, sprintf('Could not write "%s".'.PHP_EOL, $target));

    exit(1);
}
