<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

/**
 * Represents an AST-Token, which can be referenced as dependency.
 */
interface TokenInterface
{
    public function toString() : string;
}
