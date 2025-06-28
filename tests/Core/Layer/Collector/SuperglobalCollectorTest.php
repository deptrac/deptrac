<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\SuperglobalCollector;
use PHPUnit\Framework\TestCase;

final class SuperglobalCollectorTest extends TestCase
{
    private SuperglobalCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collector = new SuperglobalCollector();
    }

    public static function provideSatisfy(): iterable
    {
        yield [['value' => ['_GET', '_SESSION']], '_GET', true];
        yield [['value' => ['_COOKIE']], '_POST', false];
    }

    /**
     * @dataProvider provideSatisfy
     */
    public function testSatisfy(array $configuration, string $name, bool $expected): void
    {
        $actual = $this->collector->satisfy(
            $configuration,
            new VariableReference(SuperGlobalToken::from($name))
        );

        self::assertSame($expected, $actual);
    }

    public function testWrongRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->collector->satisfy(
            ['Foo' => 'a'],
            new VariableReference(SuperGlobalToken::from('_POST'))
        );
    }

    public function testNonVariableReferenceDoesNotSatisfy(): void
    {
        $astClassReference = new FunctionReference(FunctionToken::fromFQCN('foo'));

        $actual = $this->collector->satisfy(
            ['value' => 'abc'],
            $astClassReference,
        );

        self::assertFalse($actual);
    }
}
