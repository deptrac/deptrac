<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\NikicTypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassMethodExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ExpressionExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\NewExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\PropertyExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\VariableExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class AnnotationReferenceExtractorTest extends TestCase
{
    /**
     * @dataProvider createParser
     */
    public function testPropertyDependencyResolving(ParserInterface $parser): void
    {
        $filePath = __DIR__.'/Fixtures/AnnotationDependency.php';
        $astFileReference = $parser->parseFile($filePath);

        $astClassReferences = $astFileReference->classLikeReferences;
        $annotationDependency = $astClassReferences[0]->dependencies;

        self::assertCount(2, $astClassReferences);
        self::assertCount(9, $annotationDependency);
        self::assertCount(0, $astClassReferences[1]->dependencies);

        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\AnnotationDependencyChild',
            $annotationDependency[0]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[0]->context->fileOccurrence->filepath);
        self::assertSame(9, $annotationDependency[0]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[0]->context->dependencyType->value);

        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\AnnotationDependencyChild',
            $annotationDependency[1]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[1]->context->fileOccurrence->filepath);
        self::assertSame(23, $annotationDependency[1]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[1]->context->dependencyType->value);

        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\AnnotationDependencyChild',
            $annotationDependency[2]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[2]->context->fileOccurrence->filepath);
        self::assertSame(26, $annotationDependency[2]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[2]->context->dependencyType->value);

        self::assertSame(
            'Symfony\Component\Console\Exception\RuntimeException',
            $annotationDependency[3]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[3]->context->fileOccurrence->filepath);
        self::assertSame(29, $annotationDependency[3]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[3]->context->dependencyType->value);

        self::assertSame(
            'Symfony\Component\Finder\SplFileInfo',
            $annotationDependency[4]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[4]->context->fileOccurrence->filepath);
        self::assertSame(14, $annotationDependency[4]->context->fileOccurrence->line);
        self::assertSame('parameter', $annotationDependency[4]->context->dependencyType->value);

        self::assertSame(
            'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\AnnotationDependencyChild',
            $annotationDependency[5]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[5]->context->fileOccurrence->filepath);
        self::assertSame(14, $annotationDependency[5]->context->fileOccurrence->line);
        self::assertSame('returntype', $annotationDependency[5]->context->dependencyType->value);
    }

    /**
     * @return list<array{ParserInterface}>
     */
    public static function createParser(): array
    {
        $typeResolver = new NikicTypeResolver();
        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new PropertyExtractor($typeResolver),
            new VariableExtractor($typeResolver),
            new ExpressionExtractor($typeResolver),
            new ClassMethodExtractor($typeResolver),
            new NewExtractor($typeResolver),
        ];
        $nikicPhpParser = new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );

        return [
            'Nikic Parser' => [$nikicPhpParser],
        ];
    }
}
