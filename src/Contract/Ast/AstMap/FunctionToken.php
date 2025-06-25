<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

final class FunctionToken implements TokenInterface
{
    private function __construct(public readonly string $functionName) {}

    public static function fromFQCN(string $functionName): self
    {
        return new self(ltrim($functionName, '\\'));
    }

    public function match(string $pattern): bool
    {
        return 1 === preg_match($pattern, $this->functionName);
    }

    public function toString(): string
    {
        return $this->functionName.'()';
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self && $this->toString() === $token->toString();
    }
}
