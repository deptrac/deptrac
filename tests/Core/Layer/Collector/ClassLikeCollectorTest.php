<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\ClassLikeCollector;
use PHPUnit\Framework\TestCase;

final class ClassLikeCollectorTest extends TestCase
{
    private ClassLikeCollector $sut;

    public function setUp(): void
    {
        $this->sut = new ClassLikeCollector();
    }

    public static function dataProviderSatisfy(): iterable
    {
        yield [['value' => '^Foo\\\\Bar$'], 'Foo\\Bar', true];
        yield [['value' => '^Foo\\\\Bar$'], 'Foo\\Baz', false];
    }

    /**
     * @dataProvider dataProviderSatisfy
     */
    public function testSatisfy(array $configuration, string $className, bool $expected): void
    {
        $stat = $this->sut->satisfy(
            $configuration,
            new ClassLikeReference(ClassLikeToken::fromFQCN($className), ClassLikeType::TYPE_CLASS),
        );

        self::assertSame($expected, $stat);
    }

    public static function provideTypes(): iterable
    {
        yield 'classLike' => [ClassLikeType::TYPE_CLASSLIKE, true];
        yield 'class' => [ClassLikeType::TYPE_CLASS, true];
        yield 'interface' => [ClassLikeType::TYPE_INTERFACE, true];
        yield 'trait' => [ClassLikeType::TYPE_TRAIT, true];
    }

    /**
     * @dataProvider provideTypes
     */
    public function testSatisfyForTypes(ClassLikeType $classLikeType, bool $matches): void
    {
        $stat = $this->sut->satisfy(
            ['value' => '^Foo\\\\Bar$'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo\\Bar'), $classLikeType),
        );

        self::assertSame($matches, $stat);
    }

    public function testWrongRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->sut->satisfy(
            ['Foo' => 'a'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo'), ClassLikeType::TYPE_CLASS),
        );
    }
}
