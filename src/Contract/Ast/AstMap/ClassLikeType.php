<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

enum ClassLikeType: string implements TokenInterface
{
    case TYPE_CLASSLIKE = 'classLike';
    case TYPE_CLASS = 'class';
    case TYPE_INTERFACE = 'interface';
    case TYPE_TRAIT = 'trait';

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self && $this->toString() === $token->toString();
    }
}
