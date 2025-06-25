<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Layer\GlobCollector;
use PHPUnit\Framework\TestCase;

final class GlobCollectorTest extends TestCase
{
    private GlobCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collector = new GlobCollector(__DIR__);
    }

    public static function dataProviderSatisfy(): iterable
    {
        yield [['value' => 'foo/layer1/*'], 'foo/layer1/bar.php', true];
        yield [['value' => 'foo/*/*.php'], 'foo/layer1/bar.php', true];
        yield [['value' => 'foo/**/*'], 'foo/layer1/dir/bar.php', true];
        yield [['value' => 'foo/layer1/*'], 'foo/layer2/bar.php', false];
        yield [['value' => 'foo/layer2/*'], 'foo\\layer2\\bar.php', true];
    }

    /**
     * @dataProvider dataProviderSatisfy
     */
    public function testSatisfy(array $configuration, string $filePath, bool $expected): void
    {
        $fileReferenceBuilder = FileReferenceBuilder::create($filePath);
        $fileReferenceBuilder->newClassLike('Test', [], []);
        $fileReference = $fileReferenceBuilder->build();

        $actual = $this->collector->satisfy(
            $configuration,
            $fileReference->classLikeReferences[0],
        );

        self::assertSame($expected, $actual);
    }

    public function testWrongRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $fileReferenceBuilder = FileReferenceBuilder::create('foo/layer1/bar.php');
        $fileReferenceBuilder->newClassLike('Test', [], []);
        $fileReference = $fileReferenceBuilder->build();

        $this->collector->satisfy(
            ['Foo' => 'a'],
            $fileReference->classLikeReferences[0],
        );
    }
}
