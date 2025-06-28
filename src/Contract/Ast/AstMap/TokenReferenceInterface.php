<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * Represents the AST-Token and its location.
 */
interface TokenReferenceInterface
{
    public function getFilepath(): ?string;

    public function getToken(): TokenInterface;
}
