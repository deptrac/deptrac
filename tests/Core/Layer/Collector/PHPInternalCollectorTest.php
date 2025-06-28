<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\PhpInternalCollector;
use PHPUnit\Framework\TestCase;

final class PHPInternalCollectorTest extends TestCase
{
    /**
     * @return iterable<array{array{value:string}, TokenReferenceInterface, bool}>
     */
    public static function provideSatisfy(): iterable
    {
        yield [['value' => '^PDO'], new ClassLikeReference(ClassLikeToken::fromFQCN('PDOException')), true];
        yield [['value' => '^PFO'], new ClassLikeReference(ClassLikeToken::fromFQCN('PDOException')), false];
        yield [['value' => '.*'], new ClassLikeReference(ClassLikeToken::fromFQCN('PDOExceptionNonExistent')), false];
        yield [['value' => '^pdo'], new FunctionReference(FunctionToken::fromFQCN('pdo_drivers')), true];
        yield [['value' => '^pfo'], new FunctionReference(FunctionToken::fromFQCN('pdo_drivers')), false];
        yield [['value' => '.*'], new FunctionReference(FunctionToken::fromFQCN('pdo_drivers_non_existent')), false];
    }

    /**
     * @dataProvider provideSatisfy
     */
    public function testSatisfy(array $config, TokenReferenceInterface $reference, bool $expected): void
    {
        $collector = new PhpInternalCollector();
        $actual = $collector->satisfy(
            $config,
            $reference,
        );

        self::assertSame($expected, $actual);
    }

    public function testWrongTokenTypeDoesNotSatisfy(): void
    {
        $actual = (new PhpInternalCollector())->satisfy(
            ['value' => '/^Foo\\\\Bar$/i'],
            new class implements TokenReferenceInterface {
                public function getFilepath(): ?string
                {
                    return 'foo';
                }

                public function getToken(): TokenInterface
                {
                    return new FileToken('foo');
                }
            }
        );

        self::assertFalse($actual);

        $actual = (new PhpInternalCollector())->satisfy(
            ['value' => '/^Foo\\\\Bar$/i'],
            new VariableReference(SuperGlobalToken::GET)
        );

        self::assertFalse($actual);
    }

    public function testInvalidRegexParam(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        (new PhpInternalCollector())->satisfy(
            ['regex' => '/'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('Foo')),
        );
    }
}
