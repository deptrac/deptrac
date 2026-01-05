<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\CatchExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\PropertyExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassAttribute;
use Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassB;

final class ClassExtractorTest extends TestCase
{
    #[DataProvider('createParser')]
    public function testPropertyDependencyResolving(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/ClassExtract.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $astClassReferences = $astFileReference->classLikeReferences;

        self::assertCount(3, $astClassReferences[1]->dependencies);

        $dependencies = $astClassReferences[1]->dependencies;
        self::assertSame(
            ClassAttribute::class,
            $dependencies[0]->token->toString()
        );
        self::assertSame(DependencyType::ATTRIBUTE, $dependencies[0]->context->dependencyType);
        self::assertSame(
            ClassB::class,
            $dependencies[1]->token->toString()
        );
        self::assertSame(DependencyType::VARIABLE, $dependencies[1]->context->dependencyType);
        self::assertSame(
            'Throwable',
            $dependencies[2]->token->toString()
        );
        self::assertSame(DependencyType::CATCH, $dependencies[2]->context->dependencyType);
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
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);
        $typeResolver = new TypeResolver();
        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new PropertyExtractor($phpStanContainer, $typeResolver),
            new CatchExtractor($typeResolver),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );
    }

    public static function createPhpStanParser(string $filePath): PhpStanParser
    {
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);
        $typeResolver = new TypeResolver();

        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new PropertyExtractor($phpStanContainer, $typeResolver),
            new CatchExtractor($typeResolver),
        ];

        return new PhpStanParser($phpStanContainer, $cache, $extractors);
    }
}
