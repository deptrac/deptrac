<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;

/**
 * Resolves a token to the reference describing it.
 *
 * Implementations are registered with the `token_resolver` tag and are asked
 * in turn (highest tag priority first) whether they can resolve a given
 * token, which lets several extensions each contribute their own token
 * types. The default resolver knows the token types shipped by Deptrac and
 * runs last.
 */
interface TokenResolverInterface
{
    /**
     * Whether this resolver knows how to resolve the given token.
     */
    public function supports(TokenInterface $token): bool;

    /**
     * Only called with tokens this resolver {@see supports()}.
     *
     * @throws UnrecognizedTokenException when the token type is not recognized
     */
    public function resolve(TokenInterface $token, AstMapInterface $astMap): TokenReferenceInterface;
}
