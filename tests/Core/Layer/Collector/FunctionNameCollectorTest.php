<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\FunctionNameCollector;
use PHPUnit\Framework\TestCase;

final class FunctionNameCollectorTest extends TestCase
{
    private FunctionNameCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->collector = new FunctionNameCollector();
    }

    public static function dataProviderSatisfy(): iterable
    {
        yield [['value' => 'a'], 'foo\bar', true];
        yield [['value' => 'a'], 'foo\bbr', false];
    }

    /**
     * @dataProvider dataProviderSatisfy
     */
    public function testSatisfy(array $configuration, string $functionName, bool $expected): void
    {
        $actual = $this->collector->satisfy(
            $configuration,
            new FunctionReference(FunctionToken::fromFQCN($functionName)),
        );

        self::assertSame($expected, $actual);
    }

    public function testWrongRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->collector->satisfy(
            ['Foo' => 'a'],
            new FunctionReference(FunctionToken::fromFQCN('Foo')),
        );
    }

    public function testWrongTokenTypeDoesNotSatisfy(): void
    {
        $actual = $this->collector->satisfy(
            ['value' => 'a'],
            new VariableReference(SuperGlobalToken::GET)
        );

        self::assertFalse($actual);
    }
}
