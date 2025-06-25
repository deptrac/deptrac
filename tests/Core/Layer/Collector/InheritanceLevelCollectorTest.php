<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstException;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\DefaultBehavior\Layer\InheritanceLevelCollector;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InheritanceLevelCollectorTest extends TestCase
{
    public static function dataTests(): array
    {
        return [
            [1, 1, true],
            [2, 1, true],
            [3, 2, true],
            [1, 2, false],
            [2, 3, false],
            [3, 4, false],
        ];
    }

    /**
     * @dataProvider dataTests
     */
    public function testSatisfy(int $pathLevel, int $levelConfig, bool $expected): void
    {
        $classInherit = $this->createMock(AstInherit::class);
        $classInherit->method('getPath')
            ->willReturn(array_fill(0, $pathLevel, 1))
        ;

        $astMap = $this->createMock(AstMap::class);
        $astMap->method('getClassInherits')
            ->with(ClassLikeToken::fromFQCN(AstInherit::class))
            ->willReturn([$classInherit])
        ;

        $astMapExtractor = $this->createMock(AstMapExtractor::class);
        $astMapExtractor->method('extract')
            ->willReturn($astMap)
        ;

        $collector = new InheritanceLevelCollector($astMapExtractor);
        $actual = $collector->satisfy(
            ['value' => $levelConfig],
            new ClassLikeReference(ClassLikeToken::fromFQCN(AstInherit::class)),
        );

        self::assertSame($expected, $actual);
    }

    public function testInvalidRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $extractor = $this->createMock(AstMapExtractor::class);

        (new InheritanceLevelCollector($extractor))->satisfy(
            ['regex' => '/'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo')),
        );
    }

    public function testWrongTokenTypeDoesNotSatisfy(): void
    {
        $extractor = $this->createMock(AstMapExtractor::class);

        $actual = (new InheritanceLevelCollector($extractor))->satisfy(
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

        (new InheritanceLevelCollector($extractor))->satisfy(
            ['value' => 'App\Bar'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo')),
        );
    }
}
