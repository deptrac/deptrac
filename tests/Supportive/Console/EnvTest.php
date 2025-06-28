<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\Console\Env;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('TEST=test');
        putenv('FOO=true');
        parent::setUp();
    }

    public function testEnvPresent(): void
    {
        $env = new Env();
        self::assertSame('test', $env->get('TEST'));
        self::assertSame('true', $env->get('FOO'));
    }

    public function testEnvMissing(): void
    {
        $env = new Env();
        self::assertSame(false, $env->get('BAR'));
    }
}
