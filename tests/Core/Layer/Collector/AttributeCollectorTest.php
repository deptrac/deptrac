<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Layer\AttributeCollector;
use PHPUnit\Framework\TestCase;

final class AttributeCollectorTest extends TestCase
{
    private AttributeCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collector = new AttributeCollector();
    }

    public static function dataProviderSatisfy(): iterable
    {
        yield 'matches usage of attribute with only partial name' => [
            ['value' => 'MyAttribute'],
            true,
        ];
        yield 'does not match unescaped fully qualified class name' => [
            ['value' => 'App\MyAttribute'],
            true,
        ];
        yield 'does not match other attributes' => [
            ['value' => 'OtherAttribute'],
            false,
        ];
    }

    /**
     * @dataProvider dataProviderSatisfy
     */
    public function testSatisfy(array $config, bool $expected): void
    {
        $classLikeReference = FileReferenceBuilder::create('Foo.php')
            ->newClass('App\Foo', [], [])
            ->dependency(ClassLikeToken::fromFQCN('App\MyException'), 1, DependencyType::THROW)
            ->dependency(ClassLikeToken::fromFQCN('App\MyAttribute'), 2, DependencyType::ATTRIBUTE)
            ->dependency(ClassLikeToken::fromFQCN('MyAttribute'), 3, DependencyType::ATTRIBUTE)
            ->build()
        ;
        $actual = $this->collector->satisfy($config, $classLikeReference);

        self::assertSame($expected, $actual);
    }

    public function testWrongRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->collector->satisfy(
            ['Foo' => 'a'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo'), ClassLikeType::TYPE_CLASS),
        );
    }

    public function testWrongTokenTypeDoesNotSatisfy(): void
    {
        $actual = $this->collector->satisfy(
            ['Foo' => 'a'],
            new VariableReference(SuperGlobalToken::GET)
        );

        self::assertFalse($actual);
    }
}
