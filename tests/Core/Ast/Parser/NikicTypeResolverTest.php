<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\TypeScope;
use Deptrac\Deptrac\Core\Ast\Parser\NikicTypeResolver;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;

final class NikicTypeResolverTest extends TestCase
{
    private Lexer $lexer;
    private TypeParser $typeParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lexer = new Lexer();
        $this->typeParser = new TypeParser(new ConstExprParser());
    }

    /**
     * @dataProvider docBlockProvider
     */
    public function testResolvePHPStanDocParserType(string $doc, array $types): void
    {
        $tokens = new TokenIterator($this->lexer->tokenize($doc));
        $typeNode = $this->typeParser->parse($tokens);

        $typeResolver = new NikicTypeResolver();
        $resolvedTypes = $typeResolver->resolvePHPStanDocParserType($typeNode, new TypeScope('\\Test\\'), ['T']);

        self::assertSame($types, $resolvedTypes);
    }

    public static function docBlockProvider(): iterable
    {
        yield ['doc' => 'array<DataProviderTestSuite|TestCase>', 'types' => ['\\Test\\DataProviderTestSuite', '\\Test\\TestCase']];
        yield ['doc' => 'array<string, array<int, array<int, int|string>>>', 'types' => []];
        yield ['doc' => 'callable(A&...$a=, B&...=, C): Foo', 'types' => ['\\Test\\Foo', '\\Test\\A', '\\Test\\B', '\\Test\\C']];
        yield ['doc' => 'Foo::FOO_CONSTANT', 'types' => ['\\Test\\Foo']];
        yield ['doc' => 'array{a: Foo}', 'types' => ['\\Test\\Foo']];
        yield ['doc' => 'array-key', 'types' => []];
        yield ['doc' => 'trait-string', 'types' => []];
        yield ['doc' => 'callable-string', 'types' => []];
        yield ['doc' => 'numeric-string', 'types' => []];
        yield ['doc' => 'positive-int', 'types' => []];
        yield ['doc' => 'non-empty-array<string>', 'types' => []];
        yield ['doc' => 'callable-array', 'types' => []];
        yield ['doc' => 'list<Foo>', 'types' => ['\\Test\\Foo']];
        yield ['doc' => 'T', 'types' => []];
        yield ['doc' => 'Foo<T>', 'types' => ['\\Test\\Foo']];
        yield ['doc' => 'T<Foo>', 'types' => ['\\Test\\Foo']];
        yield ['doc' => 'Bar<Foo>', 'types' => ['\\Test\\Bar', '\\Test\\Foo']];
    }
}
