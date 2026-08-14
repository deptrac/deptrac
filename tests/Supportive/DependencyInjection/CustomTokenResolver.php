<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Dependency\TokenResolverInterface;

final class CustomTokenResolver implements TokenResolverInterface
{
    public function supports(TokenInterface $token): bool
    {
        return $token instanceof CustomToken;
    }

    public function resolve(TokenInterface $token, AstMapInterface $astMap): TokenReferenceInterface
    {
        return new ClassLikeReference(ClassLikeToken::fromFQCN($token->toString()));
    }
}
