<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Dependency;

use Qossmic\Deptrac\Contract\Ast\DependencyContext;
use Qossmic\Deptrac\Contract\Ast\TokenInterface;
use Qossmic\Deptrac\Contract\Dependency\DependencyInterface;
use Qossmic\Deptrac\Core\Ast\AstMap\AstInherit;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
class InheritDependency implements DependencyInterface
{
    public function __construct(private readonly ClassLikeToken $depender, private readonly TokenInterface $dependent, public readonly DependencyInterface $originalDependency, public readonly AstInherit $inheritPath)
    {
    }
    public function serialize() : array
    {
        $buffer = [];
        foreach ($this->inheritPath->getPath() as $p) {
            \array_unshift($buffer, ['name' => $p->classLikeName->toString(), 'line' => $p->fileOccurrence->line]);
        }
        $buffer[] = ['name' => $this->inheritPath->classLikeName->toString(), 'line' => $this->inheritPath->fileOccurrence->line];
        $buffer[] = ['name' => $this->originalDependency->getDependent()->toString(), 'line' => $this->originalDependency->getContext()->fileOccurrence->line];
        return $buffer;
    }
    public function getDepender() : ClassLikeToken
    {
        return $this->depender;
    }
    public function getDependent() : TokenInterface
    {
        return $this->dependent;
    }
    public function getContext() : DependencyContext
    {
        return $this->originalDependency->getContext();
    }
}
