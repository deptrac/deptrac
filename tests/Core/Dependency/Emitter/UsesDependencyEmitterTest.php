<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\DefaultBehavior\Dependency\UsesDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class UsesDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        self::assertSame('UsesDependencyEmitter', (new UsesDependencyEmitter())->getName());
    }

    public function testApplyDependencies(): void
    {
        $deps = $this->getEmittedDependencies(
            new UsesDependencyEmitter(),
            __DIR__.'/Fixtures/Foo.php'
        );

        self::assertCount(1, $deps);
        self::assertContains('Foo\Bar:4 on SomeUse', $deps);
    }

    public function testIgnoresNamespaces(): void
    {
        $deps = $this->getEmittedDependencies(
            new UsesDependencyEmitter(),
            [
                __DIR__.'/Fixtures/IgnoreNamespace/Deps/UsedWithFQDN.php',
                __DIR__.'/Fixtures/IgnoreNamespace/Deps/UsedWithNamespace.php',
                __DIR__.'/Fixtures/IgnoreNamespace/Deps/Functions.php',
                __DIR__.'/Fixtures/IgnoreNamespace/Uses/Foo.php',
            ]
        );

        self::assertCount(2, $deps);
        self::assertNotContains('IgnoreNamespace\Uses\Foo:5 on IgnoreNamespace\Deps', $deps);
        self::assertContains('IgnoreNamespace\Uses\Foo:6 on IgnoreNamespace\Deps\UsedWithFQDN', $deps);
        self::assertContains('IgnoreNamespace\Uses\Foo:7 on IgnoreNamespace\Deps\Functions\functionUsedWithFQDN', $deps);
    }

    public function testIncludesFQDNWhichIsAlsoANamespacePrefix(): void
    {
        $deps = $this->getEmittedDependencies(
            new UsesDependencyEmitter(),
            [
                __DIR__.'/Fixtures/FQDNNamespacePrefix/FQDN.php',
                __DIR__.'/Fixtures/FQDNNamespacePrefix/FQDN/SomeClass.php',
                __DIR__.'/Fixtures/FQDNNamespacePrefix/Uses/Foo.php',
            ]
        );

        self::assertCount(1, $deps);
        self::assertContains('FQDNNamespacePrefix\Uses\Foo:5 on FQDNNamespacePrefix\FQDN', $deps);
    }

    public function testIncludesFQDNWhichIsDoesNotExistAndSubstringOfAnotherFQDN(): void
    {
        $deps = $this->getEmittedDependencies(
            new UsesDependencyEmitter(),
            [
                __DIR__.'/Fixtures/FQDNIsSubstring/FQDN/SomeClass.php',
                __DIR__.'/Fixtures/FQDNIsSubstring/Foo.php',
            ]
        );

        self::assertCount(2, $deps);
        self::assertContains('FQDNIsSubstring\Foo:5 on FQDNIsSubstring\FQDN\Some', $deps);
        self::assertContains('FQDNIsSubstring\Foo:6 on FQDNIsSubstring\FQDN\SomeClass', $deps);
    }
}
