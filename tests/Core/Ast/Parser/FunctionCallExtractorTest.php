<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\FunctionCallExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FunctionCallExtractorTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures';

    #[DataProvider('createParser')]
    public function testRecordsTheFunctionsThatCallsInAClassMayReferTo(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/FunctionCalls.php';
        $astFileReference = $parserBuilder($filePath)->parseFile($filePath);

        self::assertCount(1, $astFileReference->classLikeReferences);
        self::assertSame(
            [
                // An unqualified name may refer to the namespaced or to the global function
                self::FIXTURE_NAMESPACE.'\unqualified():11',
                'unqualified():11',
                'fullyQualified():12',
                self::FIXTURE_NAMESPACE.'\Relative\qualified():13',
                self::FIXTURE_NAMESPACE.'\relative():14',
                'Some\Imported\imported():15',
            ],
            $this->functionCalls($astFileReference->classLikeReferences[0]->dependencies),
        );
    }

    #[DataProvider('createParser')]
    public function testRecordsTheFunctionsThatCallsInAFunctionMayReferTo(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/FunctionCalls.php';
        $astFileReference = $parserBuilder($filePath)->parseFile($filePath);

        self::assertCount(1, $astFileReference->functionReferences);
        self::assertSame(
            [
                self::FIXTURE_NAMESPACE.'\unqualified():22',
                'unqualified():22',
            ],
            $this->functionCalls($astFileReference->functionReferences[0]->dependencies),
        );
    }

    #[DataProvider('createParser')]
    public function testUnqualifiedNameInGlobalNamespaceRefersToOneFunction(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/FunctionCallsGlobalNamespace.php';
        $astFileReference = $parserBuilder($filePath)->parseFile($filePath);

        self::assertCount(1, $astFileReference->functionReferences);
        self::assertSame(
            ['unqualified():5'],
            $this->functionCalls($astFileReference->functionReferences[0]->dependencies),
        );
    }

    /**
     * @param DependencyToken[] $dependencies
     *
     * @return list<string>
     */
    private function functionCalls(array $dependencies): array
    {
        $functionCalls = [];

        foreach ($dependencies as $dependency) {
            self::assertSame(DependencyType::UNRESOLVED_FUNCTION_CALL, $dependency->context->dependencyType);

            $functionCalls[] = $dependency->token->toString().':'.$dependency->context->fileOccurrence->line;
        }

        return $functionCalls;
    }

    /**
     * @return array<string, array{Closure(string): ParserInterface}>
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
        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(),
            new AstFileReferenceInMemoryCache(),
            [new FunctionCallExtractor()],
        );
    }

    public static function createPhpStanParser(string $filePath): PhpStanParser
    {
        return new PhpStanParser(
            new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]),
            new AstFileReferenceInMemoryCache(),
            [new FunctionCallExtractor()],
        );
    }
}
