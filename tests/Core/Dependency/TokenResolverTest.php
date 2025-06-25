<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use PHPUnit\Framework\TestCase;

final class TokenResolverTest extends TestCase
{
    private TokenResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

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
}
