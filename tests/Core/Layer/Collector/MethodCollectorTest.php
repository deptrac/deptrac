<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Layer\MethodCollector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MethodCollectorTest extends TestCase
{
    private NikicPhpParser $astParser;
    private MethodCollector $collector;

    protected function setUp(): void
    {
        $this->astParser = $this->createMock(NikicPhpParser::class);

        $this->collector = new MethodCollector($this->astParser);
    }

    public static function provideSatisfy(): iterable
    {
        yield [
            ['value' => 'abc'],
            [
                'abc',
                'abcdef',
                'xyz',
            ],
            true,
        ];

        yield [
            ['value' => 'abc'],
            [
                'abc',
                'xyz',
            ],
            true,
        ];

        yield [
            ['value' => 'abc'],
            [
                'xyz',
            ],
            false,
        ];
    }

    #[DataProvider('provideSatisfy')]
    public function testSatisfy(array $configuration, array $methods, bool $expected): void
    {
        $astClassReference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));

        $this->astParser
            ->method('getMethodNamesForClassLikeReference')
            ->with($astClassReference)
            ->willReturn($methods)
        ;

        $actual = $this->collector->satisfy(
            $configuration,
            $astClassReference,
        );

        self::assertSame($expected, $actual);
    }

    public function testClassLikeAstNotFoundDoesNotSatisfy(): void
    {
        $astClassReference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));
        $this->astParser
            ->method('getMethodNamesForClassLikeReference')
            ->with($astClassReference)
            ->willReturn([])
        ;

        $actual = $this->collector->satisfy(
            ['value' => 'abc'],
            $astClassReference,
        );

        self::assertFalse($actual);
    }

    public function testNonClassReferenceDoesNotSatisfy(): void
    {
        $astClassReference = new FunctionReference(FunctionToken::fromFQCN('foo'));

        $actual = $this->collector->satisfy(
            ['value' => 'abc'],
            $astClassReference,
        );

        self::assertFalse($actual);
    }

    public function testMissingNameThrowsException(): void
    {
        $astClassReference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));

        $this->expectException(InvalidCollectorDefinitionException::class);
        $this->expectExceptionMessage('MethodCollector: Missing configuration.');

        $this->collector->satisfy(
            [],
            $astClassReference,
        );
    }

    public function testInvalidRegexParam(): void
    {
        $astClassReference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));

        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->collector->satisfy(
            ['value' => '/'],
            $astClassReference,
        );
    }

    public static function provideSatisfyMethodReference(): iterable
    {
        yield 'matches own method name' => [['value' => 'handle'], 'handleRegister', true];
        yield 'does not match other method name' => [['value' => 'build'], 'handleRegister', false];
    }

    #[DataProvider('provideSatisfyMethodReference')]
    public function testSatisfyMethodReference(array $configuration, string $methodName, bool $expected): void
    {
        $methodReference = new ClassMethodReference(
            ClassMethodToken::fromFQCNAndMethodName('foo', $methodName),
            ClassMethodVisibility::TYPE_PUBLIC,
            false,
            new FileOccurrence('foo.php', 1)
        );

        // the parser must not be consulted for method references
        $this->astParser
            ->expects(self::never())
            ->method('getMethodNamesForClassLikeReference')
        ;

        self::assertSame($expected, $this->collector->satisfy($configuration, $methodReference));
    }
}
