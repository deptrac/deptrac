<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Contract\Config;

use Deptrac\Deptrac\Contract\Config\Collector\LayerConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LayerConfigTest extends TestCase
{
    public static function provideLayerReference(): iterable
    {
        yield 'layer name' => ['Shared'];
        yield 'layer object' => [Layer::withName('Shared')];
    }

    #[DataProvider('provideLayerReference')]
    public function testCreateFromLayerReference(string|Layer $layer): void
    {
        self::assertSame(
            [
                'value' => 'Shared',
                'type' => 'layer',
                'private' => false,
            ],
            LayerConfig::create($layer)->toArray(),
        );
    }
}
