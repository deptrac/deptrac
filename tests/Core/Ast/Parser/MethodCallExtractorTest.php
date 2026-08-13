<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\MethodCallExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MethodCallExtractorTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures';

    #[DataProvider('createParser')]
    public function testSelfDispatchIsRecordedOnTheCallingMethod(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/MethodCalls.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        [$baseClass, $fixtureClass] = $astFileReference->classLikeReferences;

        [$caller, $target, $staticTarget] = $fixtureClass->methods;
        self::assertSame(self::FIXTURE_NAMESPACE.'\MethodCallsFixture::caller()', $caller->getToken()->toString());

        self::assertEqualsCanonicalizing(
            [
                self::FIXTURE_NAMESPACE.'\MethodCallsFixture::target()::14 (method_call)',
                self::FIXTURE_NAMESPACE.'\MethodCallsFixture::staticTarget()::15 (method_call)',
                self::FIXTURE_NAMESPACE.'\MethodCallsFixture::staticTarget()::16 (method_call)',
                // parent::inherited() (17) and the dynamic $this->$name() (20) stay untracked
                self::FIXTURE_NAMESPACE.'\MethodCallsFixture::target()::18 (method_call)',
                // calls inside closures belong to the enclosing method
                self::FIXTURE_NAMESPACE.'\MethodCallsFixture::target()::22 (method_call)',
            ],
            self::getDependenciesAsString($caller)
        );

        self::assertSame([], self::getDependenciesAsString($target));
        self::assertSame([], self::getDependenciesAsString($staticTarget));
        self::assertSame([], $baseClass->methods[0]->dependencies);

        // receiver is a plain variable, not $this
        self::assertSame([], $astFileReference->functionReferences[0]->dependencies);
    }

    public function testCrossClassDispatchIsResolvedWithThePhpStanParser(): void
    {
        $filePath = __DIR__.'/Fixtures/MethodCallsCrossClass.php';
        $parser = self::createPhpStanParser($filePath);
        $astFileReference = $parser->parseFile($filePath);

        [$targetClass, $callerClass] = $astFileReference->classLikeReferences;
        [$viaProperty, $viaNew, $viaStaticFactory, $viaParameter] = $callerClass->methods;

        $target = self::FIXTURE_NAMESPACE.'\CrossClassTarget';

        self::assertSame(
            ["{$target}::targetMethod()::21 (method_call)"],
            self::getDependenciesAsString($viaProperty)
        );
        self::assertSame(
            ["{$target}::targetMethod()::26 (method_call)"],
            self::getDependenciesAsString($viaNew)
        );
        self::assertSame(
            // the static create() call itself stays tracked at class level only
            ["{$target}::targetMethod()::31 (method_call)"],
            self::getDependenciesAsString($viaStaticFactory)
        );
        // limitation: parameters are unknown to the class-level scope
        self::assertSame([], self::getDependenciesAsString($viaParameter));
    }

    public function testCrossClassDispatchIsNotResolvedWithTheNikicParser(): void
    {
        $filePath = __DIR__.'/Fixtures/MethodCallsCrossClass.php';
        $parser = self::createNikicParser($filePath);
        $astFileReference = $parser->parseFile($filePath);

        foreach ($astFileReference->classLikeReferences[1]->methods as $methodReference) {
            self::assertSame([], self::getDependenciesAsString($methodReference));
        }
    }

    #[DataProvider('createParserWithoutMethodGranularity')]
    public function testNoMethodCallsAreRecordedWhenMethodGranularityIsDisabled(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/MethodCalls.php';
        $astFileReference = $parserBuilder($filePath)->parseFile($filePath);

        foreach ($astFileReference->classLikeReferences as $classReference) {
            self::assertSame([], $classReference->methods);
            self::assertSame([], $classReference->dependencies);
        }
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
        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(),
            new AstFileReferenceInMemoryCache(),
            [new MethodCallExtractor($methodGranularity)],
            $methodGranularity
        );
    }

    public static function createPhpStanParser(string $filePath, bool $methodGranularity = true): PhpStanParser
    {
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);

        return new PhpStanParser($phpStanContainer, new AstFileReferenceInMemoryCache(), [new MethodCallExtractor($methodGranularity)], $methodGranularity);
    }
}
