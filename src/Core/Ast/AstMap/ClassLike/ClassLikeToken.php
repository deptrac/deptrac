<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\AstMap\ClassLike;

use Qossmic\Deptrac\Contract\Ast\TokenInterface;
final class ClassLikeToken implements TokenInterface
{
    private function __construct(private readonly string $className)
    {
    }
    public static function fromFQCN(string $className) : self
    {
        return new self(\ltrim($className, '\\'));
    }
    public function match(string $pattern) : bool
    {
        return 1 === \preg_match($pattern, $this->className);
    }
    public function toString() : string
    {
        return $this->className;
    }
    public function equals(\Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken $classLikeName) : bool
    {
        return $this->className === $classLikeName->className;
    }
}
