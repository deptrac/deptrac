<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodSpan;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\PhpStanParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClassMethodSpanTest extends TestCase
{
    #[DataProvider('createParser')]
    public function testMethodSpansAreRecorded(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/ClassMethodSpans.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        $classReferences = [];
        foreach ($astFileReference->classLikeReferences as $classReference) {
            $classReferences[$classReference->getToken()->toString()] = $classReference;
        }

        $namespace = 'Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures';

        self::assertSame([], $classReferences[$namespace.'\SpanFixtureAttribute']->methodSpans);

        $spans = $classReferences[$namespace.'\ClassMethodSpans']->methodSpans;
        self::assertCount(3, $spans, 'methods of anonymous classes must not be recorded');

        $this->assertSpan($spans[0], 'plain', 18, 23, ClassMethodVisibility::PUBLIC, false);
        $this->assertSpan($spans[1], 'withAttribute', 25, 29, ClassMethodVisibility::PROTECTED, false);
        $this->assertSpan($spans[2], 'helper', 31, 38, ClassMethodVisibility::PRIVATE, true);

        $interfaceSpans = $classReferences[$namespace.'\ClassMethodSpansInterface']->methodSpans;
        self::assertCount(1, $interfaceSpans);
        $this->assertSpan($interfaceSpans[0], 'signatureOnly', 43, 43, ClassMethodVisibility::PUBLIC, false);

        $traitSpans = $classReferences[$namespace.'\ClassMethodSpansTrait']->methodSpans;
        self::assertCount(1, $traitSpans);
        $this->assertSpan($traitSpans[0], 'fromTrait', 48, 50, ClassMethodVisibility::PUBLIC, false);

        $enumSpans = $classReferences[$namespace.'\ClassMethodSpansEnum']->methodSpans;
        self::assertCount(1, $enumSpans);
        $this->assertSpan($enumSpans[0], 'label', 57, 60, ClassMethodVisibility::PUBLIC, false);
    }

    private function assertSpan(
        ClassMethodSpan $span,
        string $name,
        int $startLine,
        int $endLine,
        ClassMethodVisibility $visibility,
        bool $isStatic,
    ): void {
        self::assertSame($name, $span->name);
        self::assertSame($startLine, $span->startLine, sprintf('start line of %s()', $name));
        self::assertSame($endLine, $span->endLine, sprintf('end line of %s()', $name));
        self::assertSame($visibility, $span->visibility, sprintf('visibility of %s()', $name));
        self::assertSame($isStatic, $span->isStatic, sprintf('staticness of %s()', $name));
    }

    /**
     * @return array<string, list<Closure>>
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
            []
        );
    }

    public static function createPhpStanParser(string $filePath): PhpStanParser
    {
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$filePath]);

        return new PhpStanParser($phpStanContainer, new AstFileReferenceInMemoryCache(), []);
    }
}
