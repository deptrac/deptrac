<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class FunctionDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        self::assertSame('FunctionDependencyEmitter', (new FunctionDependencyEmitter())->getName());
    }

    public function testApplyDependencies(): void
    {
        $deps = $this->getEmittedDependencies(
            new FunctionDependencyEmitter(),
            __DIR__.'/Fixtures/Bar.php'
        );

        self::assertCount(29, $deps);
        self::assertContains('Foo\test():6 on Foo\SomeParam', $deps);
        self::assertContains('Foo\test():6 on Foo\SomeClass', $deps);
        self::assertContains('Foo\test():8 on Foo\SomeClass', $deps);
        self::assertContains('Foo\test():9 on SomeOtherClass', $deps);
        self::assertContains('Foo\test():11 on Foo\SomeOtherParam', $deps);
        self::assertContains('Foo\test():15 on Foo\SomeInstanceOf', $deps);
        self::assertContains('Foo\test():17 on Foo\SomeClass', $deps);
        self::assertContains('Foo\test():19 on Foo\SomeClass', $deps);
        self::assertContains('Foo\test():21 on Foo\SomeClass', $deps);
        self::assertContains('Foo\test():23 on Foo\SomeClass', $deps);
        self::assertContains('Foo\test():27 on Foo\string2', $deps);
        self::assertContains('Foo\test():31 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():38 on Foo\BarExtends', $deps);
        self::assertContains('Foo\testAnonymousClass():38 on Foo\BarInterface1', $deps);
        self::assertContains('Foo\testAnonymousClass():38 on BarInterface2', $deps);
        self::assertContains('Foo\testAnonymousClass():40 on Foo\SomeTrait', $deps);
        self::assertContains('Foo\testAnonymousClass():42 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():45 on SomeOtherClass', $deps);
        self::assertContains('Foo\testAnonymousClass():47 on Foo\SomeOtherParam', $deps);
        self::assertContains('Foo\testAnonymousClass():51 on Foo\SomeInstanceOf', $deps);
        self::assertContains('Foo\testAnonymousClass():53 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():55 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():42 on Foo\SomeParam', $deps);
        self::assertContains('Foo\testAnonymousClass():42 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():58 on Some\NamespacedClass', $deps);
        self::assertContains('Foo\testAnonymousClass():62 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():64 on Foo\SomeClass', $deps);
        self::assertContains('Foo\testAnonymousClass():68 on Foo\string2', $deps);
        self::assertContains('Foo\testAnonymousClass():72 on Foo\SomeClass', $deps);
    }
}
