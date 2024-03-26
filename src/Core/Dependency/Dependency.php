<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Dependency;

use Qossmic\Deptrac\Contract\Ast\DependencyContext;
use Qossmic\Deptrac\Contract\Ast\TokenInterface;
use Qossmic\Deptrac\Contract\Dependency\DependencyInterface;
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
