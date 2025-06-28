<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\NikicTypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class ClassDocBlockExtractorTest extends TestCase
{
    private const EXPECTED = [
        ['Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassDocBlockDependencySister', DependencyType::PARAMETER],
        ['Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassDocBlockDependencyBrother', DependencyType::RETURN_TYPE],
        ['Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassDocBlockDependencyChild', DependencyType::VARIABLE],
        ['Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassDocBlockDependencySister', DependencyType::VARIABLE],
        ['Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\ClassDocBlockDependencyBrother', DependencyType::VARIABLE],
    ];

    /**
     * @dataProvider createParser
     */
    public function testMethodResolving(ParserInterface $parser): void
    {
        $filePath = __DIR__.'/Fixtures/ClassDocBlockDependency.php';
        $astFileReference = $parser->parseFile($filePath);

        $dependencies = $astFileReference->classLikeReferences[0]->dependencies;

        self::assertCount(5, $astFileReference->classLikeReferences[0]->dependencies);

        foreach ($dependencies as $key => $dependency) {
            self::assertSame(self::EXPECTED[$key][0], $dependency->token->toString());
            self::assertSame(self::EXPECTED[$key][1], $dependency->context->dependencyType);
        }
    }

    /**
     * @return list<array{ParserInterface}>
     */
    public static function createParser(): array
    {
        $typeResolver = new NikicTypeResolver();
        $extractors = [
            new ClassLikeExtractor($typeResolver),
        ];
        $cache = new AstFileReferenceInMemoryCache();
        $parser = new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );

        return [
            'Nikic Parser' => [$parser],
        ];
    }
}
