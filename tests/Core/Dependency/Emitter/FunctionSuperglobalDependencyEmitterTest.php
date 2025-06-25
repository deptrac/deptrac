<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionSuperglobalDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class FunctionSuperglobalDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        self::assertSame('FunctionSuperglobalDependencyEmitter', (new FunctionSuperglobalDependencyEmitter())->getName());
    }

    public function testApplyDependencies(): void
    {
        $deps = $this->getEmittedDependencies(
            new FunctionSuperglobalDependencyEmitter(),
            __DIR__.'/Fixtures/Bar.php'
        );

        self::assertCount(4, $deps);
        self::assertContains('Foo\test():33 on $_SESSION', $deps);
        self::assertContains('Foo\test():34 on $_POST', $deps);
        self::assertContains('Foo\testAnonymousClass():81 on $_SESSION', $deps);
        self::assertContains('Foo\testAnonymousClass():82 on $_POST', $deps);
    }
}
