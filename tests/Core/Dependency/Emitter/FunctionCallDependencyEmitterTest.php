<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionCallDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class FunctionCallDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        self::assertSame('FunctionCallDependencyEmitter', (new FunctionCallDependencyEmitter())->getName());
    }

    public function testApplyDependencies(): void
    {
        $deps = $this->getEmittedDependencies(
            new FunctionCallDependencyEmitter(),
            __DIR__.'/Fixtures/Bar.php'
        );

        self::assertCount(1, $deps);

        self::assertContains('Foo\testAnonymousClass():86 on Foo\test()', $deps);
    }
}
