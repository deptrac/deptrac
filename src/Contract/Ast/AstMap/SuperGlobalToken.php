<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

enum SuperGlobalToken: string implements TokenInterface
{
    case GLOBALS = 'GLOBALS';
    case SERVER = '_SERVER';
    case GET = '_GET';
    case POST = '_POST';
    case FILES = '_FILES';
    case COOKIE = '_COOKIE';
    case SESSION = '_SESSION';
    case REQUEST = '_REQUEST';
    case ENV = '_ENV';

    /**
     * @return list<string>
     */
    public static function allowedNames(): array
    {
        return array_map(static fn (self $token): string => $token->value, self::cases());
    }

    public function toString(): string
    {
        return '$'.$this->value;
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self && $this->toString() === $token->toString();
    }
}
