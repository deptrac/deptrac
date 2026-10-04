<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
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

    public function testDoesNotEmitMethodCallReferences(): void
    {
        $classReference = new ClassLikeReference(
            ClassLikeToken::fromFQCN('Foo\Bar'),
            null,
            [],
            [
                new DependencyToken(
                    ClassLikeToken::fromFQCN('Foo\Baz'),
                    new DependencyContext(new FileOccurrence('/foo.php', 3), DependencyType::NEW)
                ),
                new DependencyToken(
                    ClassLikeToken::fromFQCN('self::helper()'),
                    new DependencyContext(new FileOccurrence('/foo.php', 4), DependencyType::METHOD_CALL)
                ),
            ],
        );
        $astMap = new AstMap([new FileReference('/foo.php', [$classReference], [], [])]);
        $result = new DependencyList();

        (new ClassDependencyEmitter())->applyDependencies($astMap, $result);

        $deps = array_map(
            static fn ($dependency) => $dependency->getDependent()->toString(),
            $result->getDependenciesAndInheritDependencies()
        );

        self::assertSame(['Foo\Baz'], $deps);
    }
}
