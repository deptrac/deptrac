<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\AnonymousClassExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class AnonymousClassExtractorTest extends TestCase
{
    /**
     * @dataProvider createParser
     */
    public function testPropertyDependencyResolving(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/AnonymousClass.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $astClassReferences = $astFileReference->classLikeReferences;

        self::assertCount(3, $astClassReferences);
        self::assertCount(0, $astClassReferences[0]->dependencies);
        self::assertCount(0, $astClassReferences[1]->dependencies);
        self::assertCount(2, $astClassReferences[2]->dependencies);

        $dependencies = $astClassReferences[2]->dependencies;

        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassA',
            $dependencies[0]->token->toString()
        );
        self::assertSame($filePath, $dependencies[0]->context->fileOccurrence->filepath);
        self::assertSame(19, $dependencies[0]->context->fileOccurrence->line);
        self::assertSame('anonymous_class_extends', $dependencies[0]->context->dependencyType->value);

        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\InterfaceC',
            $dependencies[1]->token->toString()
        );
        self::assertSame($filePath, $dependencies[1]->context->fileOccurrence->filepath);
        self::assertSame(19, $dependencies[1]->context->fileOccurrence->line);
        self::assertSame('anonymous_class_implements', $dependencies[1]->context->dependencyType->value);
    }

    /**
     * @return list<array{ParserInterface}>
     */
    public static function createParser(): array
    {
        return [
            'Nikic Parser' => [self::createNikicParser(...)],
        ];
    }

    public static function createNikicParser(string $filePath): NikicPhpParser
    {
        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new AnonymousClassExtractor(),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );
    }
}
