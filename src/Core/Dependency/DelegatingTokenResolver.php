<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Dependency\TokenResolverInterface;
use Deptrac\Deptrac\Contract\Dependency\UnrecognizedTokenException;

use function iterator_to_array;

/**
 * Hands a token to the first registered resolver that supports it.
 *
 * Resolvers are collected from the `token_resolver` tag, ordered by tag
 * priority, with the default {@see TokenResolver} running last. Extensions
 * that introduce their own token types add a resolver for them instead of
 * replacing the existing ones.
 */
final class DelegatingTokenResolver implements TokenResolverInterface
{
    /** @var list<TokenResolverInterface> */
    private readonly array $resolvers;

    /**
     * @param iterable<TokenResolverInterface> $resolvers
     */
    public function __construct(iterable $resolvers)
    {
        $this->resolvers = iterator_to_array($resolvers, false);
    }

    public function supports(TokenInterface $token): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws UnrecognizedTokenException
     */
    public function resolve(TokenInterface $token, AstMapInterface $astMap): TokenReferenceInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($token)) {
                return $resolver->resolve($token, $astMap);
            }
        }

        throw UnrecognizedTokenException::cannotCreateReference($token);
    }
}
