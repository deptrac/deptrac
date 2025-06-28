<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\NikicTypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\CatchExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\PropertyExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class ClassExtractorTest extends TestCase
{
    /**
     * @dataProvider createParser
     */
    public function testPropertyDependencyResolving(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/ClassExtract.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $astClassReferences = $astFileReference->classLikeReferences;

        self::assertCount(3, $astClassReferences[1]->dependencies);

        $dependencies = $astClassReferences[1]->dependencies;
        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassAttribute',
            $dependencies[0]->token->toString()
        );
        self::assertSame(DependencyType::ATTRIBUTE, $dependencies[0]->context->dependencyType);
        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassB',
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
        ];
    }

    public static function createNikicParser(string $filePath): NikicPhpParser
    {
        $typeResolver = new NikicTypeResolver();
        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new PropertyExtractor($typeResolver),
            new CatchExtractor($typeResolver),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );
    }
}
