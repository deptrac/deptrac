<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Layer\MethodCollector;
use PHPUnit\Framework\TestCase;

final class MethodCollectorTest extends TestCase
{
    private NikicPhpParser $astParser;
    private MethodCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

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

    /**
     * @dataProvider provideSatisfy
     */
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
}
