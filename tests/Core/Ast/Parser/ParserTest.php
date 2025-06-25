<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Closure;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\NikicTypeResolver;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\UseExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

final class ParserTest extends TestCase
{
    /**
     * @dataProvider createParser
     */
    public function testParseWithInvalidData(Closure $parserBuilder): void
    {
        $parser = $parserBuilder('');
        $this->expectException(TypeError::class);
        $parser->parseFile(new stdClass());
    }

    /**
     * @dataProvider createParser
     */
    public function testParseDoesNotIgnoreUsesByDefault(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/CountingUseStatements.php';
        $parser = $parserBuilder($filePath);
        self::assertCount(1, $parser->parseFile($filePath)->dependencies);
    }

    /**
     * @requires PHP >= 8.0
     *
     * @dataProvider createParser
     */
    public function testParseAttributes(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/Attributes.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);
        $astClassReferences = $astFileReference->classLikeReferences;
        self::assertCount(7, $astClassReferences[0]->dependencies);
        self::assertCount(2, $astClassReferences[1]->dependencies);
        self::assertCount(1, $astClassReferences[2]->dependencies);
    }

    /**
     * @dataProvider createParser
     */
    public function testParseTemplateTypes(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/TemplateTypes.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);
        $astClassReferences = $astFileReference->classLikeReferences;
        self::assertCount(0, $astClassReferences[0]->dependencies);
    }

    /**
     * @dataProvider createParser
     */
    public function testParseClassDocTags(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/DocTags.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        self::assertCount(2, $astFileReference->classLikeReferences);
        $classesByName = $this->refsByName($astFileReference->classLikeReferences);

        $this->assertSame(
            [
                '@internal' => [''],
                '@note' => ['Note one', 'Note two'],
            ],
            $classesByName['TaggedThing']->tags
        );
        $this->assertSame([], $classesByName['UntaggedThing']->tags);
    }

    /**
     * @dataProvider createParser
     */
    public function testParseFunctionDocTags(Closure $parserBuilder): void
    {
        $filePath = __DIR__.'/Fixtures/Functions.php';
        $parser = $parserBuilder($filePath);
        $astFileReference = $parser->parseFile($filePath);

        self::assertCount(2, $astFileReference->functionReferences);
        $functionsByName = $this->refsByName($astFileReference->functionReferences);

        $this->assertSame(
            ['@param' => ['string $foo', 'string $bar']],
            $functionsByName['taggedFunction()']->tags
        );
        $this->assertSame([], $functionsByName['untaggedFunction()']->tags);
    }

    private function refsByName(array $refs): array
    {
        $refsByName = [];

        foreach ($refs as $ref) {
            $name = preg_replace('/^.*\\\\(\w+(\(\))?)$/', '$1', $ref->getToken()->toString());
            $refsByName[$name] = $ref;
        }

        return $refsByName;
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
            new UseExtractor(),
            new ClassLikeExtractor($typeResolver),
        ];

        return new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(), $cache, $extractors
        );

        return $parser;
    }
}
