<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\TypeScope;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeResolverTest extends TestCase
{
    private Lexer $lexer;
    private TypeParser $typeParser;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(ParserConfig::class)) {
            $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
            $this->lexer = new Lexer($config);
            $constExprParser = new ConstExprParser($config);
            $this->typeParser = new TypeParser($config, $constExprParser);
        } else {
            // For phpstan/phpdoc-parser v1

            /**
             * @psalm-suppress TooFewArguments
             * @psalm-suppress InvalidArgument
             *
             * @phpstan-ignore-next-line
             */
            $this->lexer = new Lexer();

            /**
             * @psalm-suppress TooFewArguments
             * @psalm-suppress InvalidArgument
             *
             * @phpstan-ignore-next-line
             */
            $this->typeParser = new TypeParser(new ConstExprParser());
        }
    }

    #[DataProvider('docBlockProvider')]
    public function testResolvePHPStanDocParserType(string $doc, array $types): void
    {
        $tokens = new TokenIterator($this->lexer->tokenize($doc));
        $typeNode = $this->typeParser->parse($tokens);

        $typeResolver = new TypeResolver();
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
