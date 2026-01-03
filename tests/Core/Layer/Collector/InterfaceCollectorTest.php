<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\InterfaceCollector;
use Foo\Bar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InterfaceCollectorTest extends TestCase
{
    private InterfaceCollector $sut;

    public function setUp(): void
    {
        $this->sut = new InterfaceCollector();
    }

    public static function dataProviderSatisfy(): iterable
    {
        yield [['value' => '^Foo\\\\Bar$'], Bar::class, true];
        yield [['value' => '^Foo\\\\Bar$'], 'Foo\\Baz', false];
    }

    #[DataProvider('dataProviderSatisfy')]
    public function testSatisfy(array $configuration, string $className, bool $expected): void
    {
        $stat = $this->sut->satisfy(
            $configuration,
            new ClassLikeReference(ClassLikeToken::fromFQCN($className), ClassLikeType::TYPE_INTERFACE),
        );

        self::assertEquals($expected, $stat);
    }

    public static function provideTypes(): iterable
    {
        yield 'classLike' => [ClassLikeType::TYPE_CLASSLIKE, false];
        yield 'class' => [ClassLikeType::TYPE_CLASS, false];
        yield 'interface' => [ClassLikeType::TYPE_INTERFACE, true];
        yield 'trait' => [ClassLikeType::TYPE_TRAIT, false];
    }

    #[DataProvider('provideTypes')]
    public function testSatisfyTypes(ClassLikeType $classLikeType, bool $matches): void
    {
        $stat = $this->sut->satisfy(
            ['value' => '^Foo\\\\Bar$'],
            new ClassLikeReference(ClassLikeToken::fromFQCN(Bar::class), $classLikeType),
        );

        self::assertSame($matches, $stat);
    }

    public function testWrongRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->sut->satisfy(
            ['Foo' => 'a'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo'), ClassLikeType::TYPE_INTERFACE),
        );
    }
}
