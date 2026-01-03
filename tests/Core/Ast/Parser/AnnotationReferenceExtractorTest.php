<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassMethodExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ExpressionExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\NewExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\PropertyExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\VariableExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Finder\SplFileInfo;
use Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\AnnotationDependencyChild;

final class AnnotationReferenceExtractorTest extends TestCase
{
    #[DataProvider('createParser')]
    public function testPropertyDependencyResolving(ParserInterface $parser): void
    {
        $filePath = __DIR__.'/Fixtures/AnnotationDependency.php';
        $astFileReference = $parser->parseFile($filePath);

        $astClassReferences = $astFileReference->classLikeReferences;
        $annotationDependency = $astClassReferences[0]->dependencies;

        self::assertCount(5, $astClassReferences);
        self::assertCount(9, $annotationDependency);
        self::assertCount(0, $astClassReferences[1]->dependencies);
        self::assertCount(0, $astClassReferences[3]->dependencies);
        self::assertCount(0, $astClassReferences[4]->dependencies);

        self::assertSame(
            AnnotationDependencyChild::class,
            $annotationDependency[0]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[0]->context->fileOccurrence->filepath);
        self::assertSame(12, $annotationDependency[0]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[0]->context->dependencyType->value);

        self::assertSame(
            AnnotationDependencyChild::class,
            $annotationDependency[1]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[1]->context->fileOccurrence->filepath);
        self::assertSame(24, $annotationDependency[1]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[1]->context->dependencyType->value);

        self::assertSame(
            AnnotationDependencyChild::class,
            $annotationDependency[2]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[2]->context->fileOccurrence->filepath);
        self::assertSame(27, $annotationDependency[2]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[2]->context->dependencyType->value);

        self::assertSame(
            RuntimeException::class,
            $annotationDependency[3]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[3]->context->fileOccurrence->filepath);
        self::assertSame(30, $annotationDependency[3]->context->fileOccurrence->line);
        self::assertSame('variable', $annotationDependency[3]->context->dependencyType->value);

        self::assertSame(
            SplFileInfo::class,
            $annotationDependency[4]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[4]->context->fileOccurrence->filepath);
        self::assertSame(21, $annotationDependency[4]->context->fileOccurrence->line);
        self::assertSame('parameter', $annotationDependency[4]->context->dependencyType->value);

        self::assertSame(
            AnnotationDependencyChild::class,
            $annotationDependency[5]->token->toString()
        );
        self::assertSame($filePath, $annotationDependency[5]->context->fileOccurrence->filepath);
        self::assertSame(21, $annotationDependency[5]->context->fileOccurrence->line);
        self::assertSame('returntype', $annotationDependency[5]->context->dependencyType->value);
    }

    /**
     * @return list<array{ParserInterface}>
     */
    public static function createParser(): array
    {
        $typeResolver = new TypeResolver();
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, []);
        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new PropertyExtractor($phpStanContainer, $typeResolver),
            new VariableExtractor($phpStanContainer, $typeResolver),
            new ExpressionExtractor($phpStanContainer, $typeResolver),
            new ClassMethodExtractor($phpStanContainer, $typeResolver),
            new NewExtractor($typeResolver),
        ];
        $nikicPhpParser = new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );
        $phpstanParser = new PhpStanParser($phpStanContainer, $cache, $extractors);

        return [
            'Nikic Parser' => [$nikicPhpParser],
            'PHPStan Parser' => [$phpstanParser],
        ];
    }
}
