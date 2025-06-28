<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstException;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Layer\ImplementsCollector;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ImplementsCollectorTest extends TestCase
{
    public static function dataProviderSatisfy(): iterable
    {
        yield [['value' => 'App\FizTrait'], false];
        yield [['value' => 'App\Bar'], true];
        yield [['value' => 'App\Baz'], true];
        yield [['value' => 'App\Foo'], false];
        yield [['value' => 'App\None'], false];
    }

    /**
     * @dataProvider dataProviderSatisfy
     */
    public function testSatisfy(array $configuration, bool $expected): void
    {
        $fooFileReferenceBuilder = FileReferenceBuilder::create('foo.php');
        $fooFileReferenceBuilder
            ->newClassLike('App\Foo', [], [])
            ->astInherits(ClassLikeToken::fromFQCN('App\Bar'), 2, AstInheritType::IMPLEMENTS)
        ;
        $fooFileReference = $fooFileReferenceBuilder->build();

        $barFileReferenceBuilder = FileReferenceBuilder::create('bar.php');
        $barFileReferenceBuilder
            ->newClassLike('App\Bar', [], [])
            ->astInherits(ClassLikeToken::fromFQCN('App\Baz'), 2, AstInheritType::IMPLEMENTS)
        ;
        $barFileReference = $barFileReferenceBuilder->build();

        $bazFileReferenceBuilder = FileReferenceBuilder::create('baz.php');
        $bazFileReferenceBuilder->newClassLike('App\Baz', [], []);
        $bazFileReference = $bazFileReferenceBuilder->build();

        $fizTraitFileReferenceBuilder = FileReferenceBuilder::create('fiztrait.php');
        $fizTraitFileReferenceBuilder
            ->newClassLike('App\FizTrait', [], [])
        ;
        $fizTraitFileReference = $fizTraitFileReferenceBuilder->build();

        $fooBarFileReferenceBuilder = FileReferenceBuilder::create('foobar.php');
        $fooBarFileReferenceBuilder
            ->newClassLike('App\FooBar', [], [])
            ->astInherits(ClassLikeToken::fromFQCN('App\Foo'), 2, AstInheritType::EXTENDS)
            ->astInherits(ClassLikeToken::fromFQCN('App\FizTrait'), 4, AstInheritType::USES)
        ;
        $fooBarFileReference = $fooBarFileReferenceBuilder->build();

        $astMap = new AstMap(
            [$fooFileReference, $barFileReference, $bazFileReference, $fooBarFileReference, $fizTraitFileReference]
        );
        $astMapExtractor = $this->createMock(AstMapExtractor::class);
        $astMapExtractor->method('extract')
            ->willReturn($astMap)
        ;

        $collector = new ImplementsCollector($astMapExtractor);
        $actual = $collector->satisfy(
            $configuration,
            $fooBarFileReference->classLikeReferences[0]
        );

        self::assertSame($expected, $actual);
    }

    public function testInvalidRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $extractor = $this->createMock(AstMapExtractor::class);

        (new ImplementsCollector($extractor))->satisfy(
            ['regex' => '/'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo')),
        );
    }

    public function testWrongTokenTypeDoesNotSatisfy(): void
    {
        $extractor = $this->createMock(AstMapExtractor::class);

        $actual = (new ImplementsCollector($extractor))->satisfy(
            ['value' => '/^Foo\\\\Bar$/i'],
            new VariableReference(SuperGlobalToken::GET)
        );

        self::assertFalse($actual);
    }

    public function testFailedAstExtraction(): void
    {
        $this->expectException(CouldNotParseFileException::class);

        $extractor = $this->createMock(AstMapExtractor::class);
        $extractor
            ->method('extract')
            ->willThrowException(AstException::couldNotCollectFiles(new RuntimeException('')))
        ;

        (new ImplementsCollector($extractor))->satisfy(
            ['value' => 'App\Bar'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo')),
        );
    }
}
