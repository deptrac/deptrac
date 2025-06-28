<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\NikicTypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\FunctionLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

final class FunctionLikeExtractorTest extends TestCase
{
    /**
     * @dataProvider createParser
     */
    public function testPropertyDependencyResolving(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/MethodSignatures.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $astClassReferences = $astFileReference->classLikeReferences;

        self::assertCount(4, $astClassReferences);
        [$attribute, $classA, $classB, $classC] = $astClassReferences;

        self::assertEqualsCanonicalizing(
            [
                'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\MethodAttribute::9 (attribute)',
            ],
            $this->getDependenciesAsString($classA)
        );

        self::assertEqualsCanonicalizing(
            [
                'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\MethodSignaturesA::15 (returntype)',
            ],
            $this->getDependenciesAsString($classB)
        );

        self::assertEqualsCanonicalizing(
            [
                'SensitiveParameter::24 (attribute)',
                'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures\MethodSignaturesB::24 (parameter)',
                // NOTE: We are not yet tracking the call from MethodSignatureC::test()
                // to MethodSignatureA::foo().
            ],
            $this->getDependenciesAsString($classC)
        );
    }

    /**
     * @return string[]
     */
    private function getDependenciesAsString(?ClassLikeReference $classReference): array
    {
        if (null === $classReference) {
            return [];
        }

        return array_map(
            static function (DependencyToken $dependency) {
                return "{$dependency->token->toString()}::{$dependency->context->fileOccurrence->line} ({$dependency->context->dependencyType->value})";
            },
            $classReference->dependencies
        );
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
            new FunctionLikeExtractor($typeResolver),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );
    }
}
