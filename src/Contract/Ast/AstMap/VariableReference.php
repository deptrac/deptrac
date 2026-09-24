<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 */
final readonly class VariableReference implements TokenReferenceInterface
{
    public function __construct(private SuperGlobalToken $tokenName) {}

    public function getFilepath(): ?string
    {
        return null;
    }

    public function getToken(): TokenInterface
    {
        return $this->tokenName;
    }
}
