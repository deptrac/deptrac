<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\TokenInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
class Dependency implements DependencyInterface
{
    public function __construct(private readonly TokenInterface $depender, private readonly TokenInterface $dependent, private readonly DependencyContext $context)
    {
    }
    public function serialize() : array
    {
        return [['name' => $this->dependent->toString(), 'line' => $this->context->fileOccurrence->line]];
    }
    public function getDepender() : TokenInterface
    {
        return $this->depender;
    }
    public function getDependent() : TokenInterface
    {
        return $this->dependent;
    }
    public function getContext() : DependencyContext
    {
        return $this->context;
    }
}
