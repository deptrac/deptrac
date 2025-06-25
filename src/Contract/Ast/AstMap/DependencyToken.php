<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 */
final class DependencyToken
{
    public function __construct(
        public readonly TokenInterface $token,
        public readonly DependencyContext $context,
    ) {}
}
