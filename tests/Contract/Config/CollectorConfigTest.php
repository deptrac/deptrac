<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Contract\Config;

use Deptrac\Deptrac\Contract\Config\Collector\AttributeConfig;
use Deptrac\Deptrac\Contract\Config\Collector\BoolConfig;
use Deptrac\Deptrac\Contract\Config\Collector\MethodConfig;
use PHPUnit\Framework\TestCase;

final class CollectorConfigTest extends TestCase
{
    public function testScopeIsOmittedByDefault(): void
    {
        self::assertSame(
            [
                'value' => 'SomeAttribute',
                'type' => 'attribute',
                'private' => false,
            ],
            AttributeConfig::create('SomeAttribute')->toArray(),
        );
    }

    public function testForMethodsAddsMethodScope(): void
    {
        self::assertSame(
            [
                'value' => 'SomeAttribute',
                'type' => 'attribute',
                'private' => false,
                'scope' => 'method',
            ],
            AttributeConfig::create('SomeAttribute')->forMethods()->toArray(),
        );
    }

    public function testForMethodsOnBoolConfig(): void
    {
        $config = BoolConfig::create()
            ->must(MethodConfig::create('handle'))
            ->forMethods()
            ->toArray()
        ;

        self::assertSame('method', $config['scope']);
    }
}
