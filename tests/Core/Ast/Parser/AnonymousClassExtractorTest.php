<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\AnonymousClassExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassA;
use Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\InterfaceC;

final class AnonymousClassExtractorTest extends TestCase
{
    #[DataProvider('createParser')]
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
            ClassA::class,
            $dependencies[0]->token->toString()
        );
        self::assertSame($filePath, $dependencies[0]->context->fileOccurrence->filepath);
        self::assertSame(19, $dependencies[0]->context->fileOccurrence->line);
        self::assertSame('anonymous_class_extends', $dependencies[0]->context->dependencyType->value);

        self::assertSame(
            InterfaceC::class,
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
            'PHPStan Parser' => [self::createPhpStanParser(...)],
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

    public static function createPhpStanParser(string $filePath): PhpStanParser
    {
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);

        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new AnonymousClassExtractor(),
        ];

        return new PhpStanParser($phpStanContainer, $cache, $extractors);
    }
}
