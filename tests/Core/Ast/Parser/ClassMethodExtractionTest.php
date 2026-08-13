<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ExpressionExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\FunctionLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\NewExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClassMethodExtractionTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures';

    #[DataProvider('createParser')]
    public function testMethodReferencesAreExtracted(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/MethodGranularity.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $classReferences = $astFileReference->classLikeReferences;
        self::assertCount(5, $classReferences);

        $featureClass = $classReferences[4];
        self::assertSame(self::FIXTURE_NAMESPACE.'\GranularFeature', $featureClass->getToken()->toString());
        self::assertCount(3, $featureClass->methods);

        [$handle, $build, $audit] = $featureClass->methods;

        self::assertSame(self::FIXTURE_NAMESPACE.'\GranularFeature::handle()', $handle->getToken()->toString());
        self::assertSame(self::FIXTURE_NAMESPACE.'\GranularFeature', $handle->getToken()->getClassToken()->toString());
        self::assertSame('handle', $handle->getToken()->getMethodName());
        self::assertSame(ClassMethodVisibility::TYPE_PUBLIC, $handle->visibility);
        self::assertFalse($handle->isStatic);
        self::assertSame($filePath, $handle->getFilepath());
        // the declaration starts at the method's first attribute
        self::assertSame(21, $handle->declaration->line);
        self::assertTrue($handle->hasTag('@granular-tag'));
        self::assertSame(['handles registration'], $handle->getTagLines('@granular-tag'));
        self::assertEqualsCanonicalizing(
            [
                self::FIXTURE_NAMESPACE.'\GranularMethodAttribute::21 (attribute)',
                self::FIXTURE_NAMESPACE.'\GranularDependencyA::22 (parameter)',
                self::FIXTURE_NAMESPACE.'\GranularDependencyB::25 (new)',
            ],
            self::getDependenciesAsString($handle)
        );

        self::assertSame(self::FIXTURE_NAMESPACE.'\GranularFeature::build()', $build->getToken()->toString());
        self::assertSame(ClassMethodVisibility::TYPE_PROTECTED, $build->visibility);
        self::assertTrue($build->isStatic);

        self::assertSame(self::FIXTURE_NAMESPACE.'\GranularFeature::audit()', $audit->getToken()->toString());
        self::assertSame(ClassMethodVisibility::TYPE_PRIVATE, $audit->visibility);
        self::assertFalse($audit->isStatic);
        self::assertEqualsCanonicalizing(
            [
                self::FIXTURE_NAMESPACE.'\GranularDependencyA::29 (returntype)',
                // methods of anonymous classes belong to the enclosing method
                self::FIXTURE_NAMESPACE.'\GranularDependencyB::34 (new)',
            ],
            self::getDependenciesAsString($build)
        );
    }

    #[DataProvider('createParser')]
    public function testMethodDependenciesAreMirroredOnClassReference(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/MethodGranularity.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $featureClass = $astFileReference->classLikeReferences[4];

        // the class keeps the complete dependency list, including method-owned dependencies
        self::assertCount(6, $featureClass->dependencies);
        self::assertContains(
            self::FIXTURE_NAMESPACE.'\GranularClassAttribute::15 (attribute)',
            array_map(
                static fn (DependencyToken $dependency): string => "{$dependency->token->toString()}::{$dependency->context->fileOccurrence->line} ({$dependency->context->dependencyType->value})",
                $featureClass->dependencies
            )
        );

        foreach ($featureClass->methods as $method) {
            foreach ($method->dependencies as $dependency) {
                self::assertContains($dependency, $featureClass->dependencies);
            }
        }
    }

    public function testMethodTemplatesAreNotReportedAsDependencies(): void
    {
        $filePath = __DIR__.'/Fixtures/MethodTemplates.php';
        $typeResolver = new TypeResolver();
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);
        $parser = new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(),
            new AstFileReferenceInMemoryCache(),
            [new ExpressionExtractor($phpStanContainer, $typeResolver)]
        );

        $astFileReference = $parser->parseFile($filePath);
        [$method] = $astFileReference->classLikeReferences[0]->methods;

        // the method's @template name must not resolve to a class dependency
        self::assertSame([], $method->dependencies);
    }

    #[DataProvider('createParserWithoutMethodGranularity')]
    public function testMethodReferencesAreNotExtractedWhenMethodGranularityIsDisabled(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/MethodGranularity.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $featureClass = $astFileReference->classLikeReferences[4];
        self::assertSame(self::FIXTURE_NAMESPACE.'\GranularFeature', $featureClass->getToken()->toString());
        self::assertSame([], $featureClass->methods);

        // the class still records the complete dependency list
        self::assertCount(6, $featureClass->dependencies);
    }

    /**
     * @return string[]
     */
    private static function getDependenciesAsString(ClassMethodReference $methodReference): array
    {
        return array_map(
            static fn (DependencyToken $dependency): string => "{$dependency->token->toString()}::{$dependency->context->fileOccurrence->line} ({$dependency->context->dependencyType->value})",
            $methodReference->dependencies
        );
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

    /**
     * @return list<array{ParserInterface}>
     */
    public static function createParserWithoutMethodGranularity(): array
    {
        return [
            'Nikic Parser' => [static fn (string $filePath): NikicPhpParser => self::createNikicParser($filePath, false)],
            'PHPStan Parser' => [static fn (string $filePath): PhpStanParser => self::createPhpStanParser($filePath, false)],
        ];
    }

    public static function createNikicParser(string $filePath, bool $methodGranularity = true): NikicPhpParser
    {
        $typeResolver = new TypeResolver();
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);

        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new ClassLikeExtractor($phpStanContainer, $typeResolver),
            new FunctionLikeExtractor($typeResolver),
            new NewExtractor($typeResolver),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors, $methodGranularity
        );
    }

    public static function createPhpStanParser(string $filePath, bool $methodGranularity = true): PhpStanParser
    {
        $typeResolver = new TypeResolver();
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);

        $cache = new AstFileReferenceInMemoryCache();
        $extractors = [
            new ClassLikeExtractor($phpStanContainer, $typeResolver),
            new FunctionLikeExtractor($typeResolver),
            new NewExtractor($typeResolver),
        ];

        return new PhpStanParser($phpStanContainer, $cache, $extractors, $methodGranularity);
    }
}
