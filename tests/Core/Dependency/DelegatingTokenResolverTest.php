<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Dependency\TokenResolverInterface;
use Deptrac\Deptrac\Contract\Dependency\UnrecognizedTokenException;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Dependency\DelegatingTokenResolver;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use PHPUnit\Framework\TestCase;

final class DelegatingTokenResolverTest extends TestCase
{
    public function testDelegatesToTheFirstSupportingResolver(): void
    {
        $resolver = new DelegatingTokenResolver([
            self::resolverFor(CustomTokenA::class, 'first'),
            self::resolverFor(CustomTokenA::class, 'second'),
            new TokenResolver(),
        ]);

        $resolved = $resolver->resolve(new CustomTokenA(), new AstMap([]));

        self::assertSame('first', $resolved->getToken()->toString());
    }

    public function testResolversOfDifferentExtensionsCoexist(): void
    {
        $resolver = new DelegatingTokenResolver([
            self::resolverFor(CustomTokenA::class, 'from-a'),
            self::resolverFor(CustomTokenB::class, 'from-b'),
            new TokenResolver(),
        ]);
        $astMap = new AstMap([]);

        self::assertSame('from-a', $resolver->resolve(new CustomTokenA(), $astMap)->getToken()->toString());
        self::assertSame('from-b', $resolver->resolve(new CustomTokenB(), $astMap)->getToken()->toString());
        self::assertSame(
            'App\\Foo',
            $resolver->resolve(ClassLikeToken::fromFQCN('App\\Foo'), $astMap)->getToken()->toString()
        );
    }

    public function testSupportsIsDelegatedToTheRegisteredResolvers(): void
    {
        $resolver = new DelegatingTokenResolver([self::resolverFor(CustomTokenA::class, 'from-a')]);

        self::assertTrue($resolver->supports(new CustomTokenA()));
        self::assertFalse($resolver->supports(new CustomTokenB()));
    }

    public function testThrowsWhenNoResolverSupportsTheToken(): void
    {
        $resolver = new DelegatingTokenResolver([new TokenResolver()]);

        $this->expectException(UnrecognizedTokenException::class);

        $resolver->resolve(new CustomTokenA(), new AstMap([]));
    }

    /**
     * @param class-string<TokenInterface> $tokenType
     */
    private static function resolverFor(string $tokenType, string $resolvedName): TokenResolverInterface
    {
        return new class($tokenType, $resolvedName) implements TokenResolverInterface {
            /**
             * @param class-string<TokenInterface> $tokenType
             */
            public function __construct(
                private readonly string $tokenType,
                private readonly string $resolvedName,
            ) {}

            public function supports(TokenInterface $token): bool
            {
                return $token instanceof $this->tokenType;
            }

            public function resolve(TokenInterface $token, AstMapInterface $astMap): TokenReferenceInterface
            {
                return new ClassLikeReference(ClassLikeToken::fromFQCN($this->resolvedName));
            }
        };
    }
}

final class CustomTokenA implements TokenInterface
{
    public function toString(): string
    {
        return 'custom-token-a';
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self;
    }
}

final class CustomTokenB implements TokenInterface
{
    public function toString(): string
    {
        return 'custom-token-b';
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self;
    }
}
