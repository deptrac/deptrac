<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

/**
 * Represents the AST-Token and its location.
 */
interface TokenReferenceInterface
{
    public function getFilepath() : ?string;
    public function getToken() : \Deptrac\Deptrac\Contract\Ast\TokenInterface;
}
