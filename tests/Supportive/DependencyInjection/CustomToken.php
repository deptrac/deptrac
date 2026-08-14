<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;

final class CustomToken implements TokenInterface
{
    public function toString(): string
    {
        return 'CustomToken';
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self;
    }
}
