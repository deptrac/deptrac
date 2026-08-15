<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 */
final readonly class DependencyToken
{
    public function __construct(
        public TokenInterface $token,
        public DependencyContext $context,
    ) {}
}
