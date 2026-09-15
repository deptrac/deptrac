<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Cache\Fixtures;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;

final class CustomCachedToken implements TokenInterface
{
    public function __construct(public readonly string $name) {}

    public function toString(): string
    {
        return $this->name;
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self && $token->name === $this->name;
    }
}
