<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\InternalIOCollector;
use FTP\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InternalIOCollectorTest extends TestCase
{
    private InternalIOCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new InternalIOCollector();
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function provideFunctions(): iterable
    {
        yield 'returns a resource' => ['fopen', true];
        yield 'consumes a resource' => ['fwrite', true];
        yield 'only has an optional stream context' => ['file_get_contents', true];
        yield 'optional stream context, returns nothing of interest' => ['unlink', true];
        yield 'stream function' => ['stream_socket_client', true];
        yield 'returns a handle class that replaced a resource' => ['curl_init', true];
        yield 'consumes a handle class that replaced a resource' => ['curl_exec', true];
        yield 'consumes a namespaced handle class' => ['ftp_login', true];
        yield 'handle class without language level information in the stubs' => ['socket_write', true];
        yield 'namespaced internal function' => ['Brotli\compress_init', true];
        yield 'fully qualified' => ['\fopen', true];
        yield 'function names are case-insensitive' => ['FOpen', true];

        yield 'internal function without a resource' => ['strlen', false];
        yield 'touches the filesystem without a resource' => ['file_exists', false];
        yield 'GMP numbers are not handles' => ['gmp_add', false];
        yield 'function in another namespace' => ['App\fopen', false];
        yield 'not an internal function' => ['App\Domain\calculate', false];
    }

    #[DataProvider('provideFunctions')]
    public function testSatisfyForFunction(string $functionName, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->collector->satisfy([], new FunctionReference(FunctionToken::fromFQCN($functionName))),
        );
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function provideClassLikes(): iterable
    {
        yield 'handle class that replaced a resource' => ['CurlHandle', true];
        yield 'namespaced handle class' => [Connection::class, true];
        yield 'method accepts a stream context' => ['SplFileObject', true];
        yield 'method returns a resource' => ['ZipArchive', true];
        yield 'inherits from a class that performs I/O' => ['SplTempFileObject', true];
        yield 'fully qualified' => ['\CurlHandle', true];
        yield 'class names are case-insensitive' => ['curlhandle', true];

        yield 'internal class without a resource' => ['DateTimeImmutable', false];
        yield 'GMP numbers are not handles' => ['GMP', false];
        yield 'class in another namespace' => ['App\CurlHandle', false];
        yield 'not an internal class' => ['App\Domain\Order', false];
    }

    #[DataProvider('provideClassLikes')]
    public function testSatisfyForClassLike(string $className, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->collector->satisfy([], new ClassLikeReference(ClassLikeToken::fromFQCN($className))),
        );
    }

    /**
     * @return iterable<string, array{TokenReferenceInterface}>
     */
    public static function provideOtherReferences(): iterable
    {
        yield 'file named like a function' => [new FileReference('fopen', [], [], [])];
        yield 'file named like a class' => [new FileReference('CurlHandle', [], [], [])];
        yield 'superglobal' => [new VariableReference(SuperGlobalToken::GET)];
    }

    #[DataProvider('provideOtherReferences')]
    public function testOtherReferencesDoNotSatisfy(TokenReferenceInterface $reference): void
    {
        self::assertFalse($this->collector->satisfy([], $reference));
    }

    /**
     * @return iterable<string, array{string, TokenReferenceInterface, bool}>
     */
    public static function provideExcludes(): iterable
    {
        $function = static fn (string $name): FunctionReference => new FunctionReference(FunctionToken::fromFQCN($name));
        $classLike = static fn (string $name): ClassLikeReference => new ClassLikeReference(ClassLikeToken::fromFQCN($name));

        yield 'excluded function' => ['^get_resource_(type|id)$', $function('get_resource_type'), false];
        yield 'function that is not excluded' => ['^get_resource_(type|id)$', $function('fopen'), true];
        yield 'pattern is not anchored' => ['resource', $function('get_resource_type'), false];
        yield 'pattern is case-insensitive' => ['^IMAGE', $function('imagepng'), false];
        yield 'excluded class' => ['^Gd', $classLike('GdImage'), false];
        yield 'class that is not excluded' => ['^Gd', $classLike('CurlHandle'), true];
        yield 'namespaced class' => ['^FTP\\\\', $classLike(Connection::class), false];
        yield 'exclude does not add anything' => ['^fopen$', $function('strlen'), false];
        yield 'empty pattern excludes nothing' => ['', $function('fopen'), true];
    }

    #[DataProvider('provideExcludes')]
    public function testExclude(string $exclude, TokenReferenceInterface $reference, bool $expected): void
    {
        self::assertSame($expected, $this->collector->satisfy(['exclude' => $exclude], $reference));
    }

    public function testExcludeHasToBeAString(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);
        $this->expectExceptionMessage('InternalIOCollector: "exclude" is not a string.');

        $this->collector->satisfy(
            ['exclude' => ['fopen' => 'fclose']],
            new FunctionReference(FunctionToken::fromFQCN('fopen')),
        );
    }

    public function testExcludeHasToBeAValidRegularExpression(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);
        $this->expectExceptionMessage('InternalIOCollector: Invalid regex pattern /(/i');

        $this->collector->satisfy(
            ['exclude' => '('],
            new FunctionReference(FunctionToken::fromFQCN('fopen')),
        );
    }

    public function testExcludeIsNotValidatedForReferencesThatAreNotCollectedAnyway(): void
    {
        self::assertFalse($this->collector->satisfy(
            ['exclude' => '('],
            new FunctionReference(FunctionToken::fromFQCN('strlen')),
        ));
    }
}
