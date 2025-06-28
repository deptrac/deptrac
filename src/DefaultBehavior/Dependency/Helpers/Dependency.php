<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;

final class Dependency implements DependencyInterface
{
    public function __construct(
        private readonly TokenInterface $depender,
        private readonly TokenInterface $dependent,
        private readonly DependencyContext $context,
    ) {}

    public function serialize(): array
    {
        return [[
            'name' => $this->dependent->toString(),
            'line' => $this->context->fileOccurrence->line,
        ]];
    }

    public function getDepender(): TokenInterface
    {
        return $this->depender;
    }

    public function getDependent(): TokenInterface
    {
        return $this->dependent;
    }

    public function getContext(): DependencyContext
    {
        return $this->context;
    }
}
