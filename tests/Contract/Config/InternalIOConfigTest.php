<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Contract\Config;

use Deptrac\Deptrac\Contract\Config\Collector\InternalIOConfig;
use PHPUnit\Framework\TestCase;

final class InternalIOConfigTest extends TestCase
{
    public function testCreate(): void
    {
        self::assertSame(
            [
                'type' => 'internalIO',
                'private' => false,
            ],
            InternalIOConfig::create()->toArray(),
        );
    }

    public function testCreateWithExclude(): void
    {
        self::assertSame(
            [
                'type' => 'internalIO',
                'private' => false,
                'exclude' => '^image',
            ],
            InternalIOConfig::create('^image')->toArray(),
        );
    }

    public function testExclude(): void
    {
        self::assertSame(
            [
                'type' => 'internalIO',
                'private' => true,
                'exclude' => '^hash_',
            ],
            InternalIOConfig::create('^image')->exclude('^hash_')->private()->toArray(),
        );
    }
}
