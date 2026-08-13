<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 */
final class ClassMethodToken implements TokenInterface
{
    private function __construct(
        private readonly ClassLikeToken $classLikeToken,
        private readonly string $methodName,
    ) {}

    public static function fromFQCNAndMethodName(string $className, string $methodName): self
    {
        return new self(ClassLikeToken::fromFQCN($className), $methodName);
    }

    public static function fromClassTokenAndMethodName(ClassLikeToken $classLikeToken, string $methodName): self
    {
        return new self($classLikeToken, $methodName);
    }

    public function getClassToken(): ClassLikeToken
    {
        return $this->classLikeToken;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function match(string $pattern): bool
    {
        return 1 === preg_match($pattern, $this->toString());
    }

    public function toString(): string
    {
        return $this->classLikeToken->toString().'::'.$this->methodName.'()';
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self && $this->toString() === $token->toString();
    }
}
