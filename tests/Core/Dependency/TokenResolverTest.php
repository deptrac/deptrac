<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodVisibility;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;
use PHPUnit\Framework\TestCase;

final class TokenResolverTest extends TestCase
{
    private TokenResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new TokenResolver();
    }

    public function testResolvesClassLikeNotInAstMap(): void
    {
        $astMap = new AstMap([]);
        $token = ClassLikeToken::fromFQCN('App\\Foo');

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(ClassLikeReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesClassLikeFromAstMap(): void
    {
        $token = ClassLikeToken::fromFQCN('App\\Foo');
        $classReference = new ClassLikeReference($token);
        $fileReference = new FileReference(
            'path/to/file.php',
            [
                $classReference,
            ],
            [],
            []
        );
        $astMap = new AstMap([$fileReference]);

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(ClassLikeReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesFunctionNotInAstMap(): void
    {
        $astMap = new AstMap([]);
        $token = FunctionToken::fromFQCN('App\\Foo::foo');

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(FunctionReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesFunctionFromAstMap(): void
    {
        $token = FunctionToken::fromFQCN('App\\Foo::foo');
        $functionReference = new FunctionReference($token);
        $fileReference = new FileReference(
            'path/to/file.php',
            [],
            [
                $functionReference,
            ],
            []
        );
        $astMap = new AstMap([$fileReference]);

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(FunctionReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesSuperglobal(): void
    {
        $astMap = new AstMap([]);
        $token = SuperGlobalToken::from('_POST');

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(VariableReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesFileNotInAstMap(): void
    {
        $astMap = new AstMap([]);
        $token = new FileToken('path/to/file.php');

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(FileReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesFileFromAstMap(): void
    {
        $fileReference = new FileReference(
            'path/to/file.php',
            [],
            [],
            []
        );
        $astMap = new AstMap([$fileReference]);
        $token = new FileToken('path/to/file.php');

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(FileReference::class, $resolved);
        self::assertSame($token->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesClassMethodFromAstMap(): void
    {
        $methodToken = ClassMethodToken::fromFQCNAndMethodName('App\Foo', 'bar');
        $methodReference = new ClassMethodReference(
            $methodToken,
            ClassMethodVisibility::TYPE_PUBLIC,
            false,
            new FileOccurrence('path/to/file.php', 3)
        );
        $classReference = new ClassLikeReference(
            ClassLikeToken::fromFQCN('App\Foo'),
            null,
            [],
            [],
            [],
            null,
            [$methodReference]
        );
        $fileReference = new FileReference('path/to/file.php', [$classReference], [], []);
        $astMap = new AstMap([$fileReference]);

        $resolved = $this->resolver->resolve($methodToken, $astMap);

        self::assertSame('path/to/file.php', $resolved->getFilepath());
        self::assertSame($methodToken->toString(), $resolved->getToken()->toString());
    }

    public function testResolvesClassMethodNotInAstMap(): void
    {
        $astMap = new AstMap([]);
        $token = ClassMethodToken::fromFQCNAndMethodName('App\Foo', 'unknownMethod');

        $resolved = $this->resolver->resolve($token, $astMap);

        self::assertInstanceOf(ClassMethodReference::class, $resolved);
        self::assertNull($resolved->getFilepath());
        self::assertSame($token->toString(), $resolved->getToken()->toString());
        self::assertSame(ClassMethodVisibility::TYPE_PUBLIC, $resolved->visibility);
        self::assertFalse($resolved->isStatic);
    }

    public function testThrowsOnUnrecognizedToken(): void
    {
        $astMap = new AstMap([]);
        $token = new class implements TokenInterface {
            public function toString(): string
            {
                return 'custom-token';
            }

            public function equals(TokenInterface $token): bool
            {
                return false;
            }
        };

        $this->expectException(UnrecognizedTokenException::class);

        $this->resolver->resolve($token, $astMap);
    }
}
