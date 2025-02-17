<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Ast;

/**
 * Represents the AST-Token and its location.
 */
interface TokenReferenceInterface
{
    public function getFilepath() : ?string;
    public function getToken() : \Qossmic\Deptrac\Contract\Ast\TokenInterface;
}
