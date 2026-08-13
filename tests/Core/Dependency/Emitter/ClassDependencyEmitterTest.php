<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\DefaultBehavior\Dependency\ClassDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class ClassDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        self::assertSame('ClassDependencyEmitter', (new ClassDependencyEmitter())->getName());
    }

    public function testApplyDependencies(): void
    {
        $deps = $this->getEmittedDependencies(
            new ClassDependencyEmitter(),
            __DIR__.'/Fixtures/Foo.php'
        );

        self::assertCount(18, $deps);
        self::assertContains('Foo\Bar:6 on Foo\BarExtends', $deps);
        self::assertContains('Foo\Bar:6 on Foo\BarInterface1', $deps);
        self::assertContains('Foo\Bar:6 on BarInterface2', $deps);
        self::assertContains('Foo\Bar:8 on Foo\SomeTrait', $deps);
        self::assertContains('Foo\Bar:10 on Foo\SomeParam', $deps);
        self::assertContains('Foo\Bar:10 on Foo\SomeClass', $deps);
        self::assertContains('Foo\Bar:12 on Foo\SomeClass', $deps);
        self::assertContains('Foo\Bar:13 on SomeOtherClass', $deps);
        self::assertContains('Foo\Bar:15 on Foo\SomeOtherParam', $deps);
        self::assertContains('Foo\Bar:19 on Foo\SomeInstanceOf', $deps);
        self::assertContains('Foo\Bar:21 on Foo\SomeClass', $deps);
        self::assertContains('Foo\Bar:23 on Foo\SomeClass', $deps);
        self::assertContains('Foo\Bar:26 on Some\NamespacedClass', $deps);
        self::assertContains('Foo\Bar:30 on Foo\SomeClass', $deps);
        self::assertContains('Foo\Bar:32 on Foo\SomeClass', $deps);
        self::assertContains('Foo\Bar:36 on Foo\string2', $deps);
        self::assertContains('Foo\Bar:42 on Foo\SomeClass', $deps);
    }

    public function testDoesNotEmitIntraClassMethodCalls(): void
    {
        $deps = $this->getEmittedDependencies(
            new ClassDependencyEmitter(),
            __DIR__.'/Fixtures/MethodCalls.php'
        );

        self::assertSame(['Foo\MethodCallClass:11 on Foo\SomeClass'], $deps);
    }

    public function testSkipsMethodDependenciesAtMethodGranularity(): void
    {
        $deps = $this->getEmittedDependencies(
            new ClassDependencyEmitter(['types' => ['class', 'method']]),
            __DIR__.'/Fixtures/MethodGranularity.php'
        );

        // every dependency of the fixture lives inside a method, so the method emitter owns them all
        self::assertSame([], $deps);
    }

    public function testEmitsMethodDependenciesAtClassGranularity(): void
    {
        $deps = $this->getEmittedDependencies(
            new ClassDependencyEmitter(['types' => ['class']]),
            __DIR__.'/Fixtures/MethodGranularity.php'
        );

        self::assertEqualsCanonicalizing(
            [
                'Foo\MethodEmitterClass:7 on Foo\SomeParam',
                'Foo\MethodEmitterClass:10 on Foo\SomeClass',
                'Foo\MethodEmitterClass:15 on Foo\OtherClass',
            ],
            $deps
        );
    }
}
