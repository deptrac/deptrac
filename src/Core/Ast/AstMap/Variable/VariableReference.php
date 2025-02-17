<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\AstMap\Variable;

use Qossmic\Deptrac\Contract\Ast\TokenInterface;
use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
/**
 * @psalm-immutable
 */
class VariableReference implements TokenReferenceInterface
{
    public function __construct(private readonly \Qossmic\Deptrac\Core\Ast\AstMap\Variable\SuperGlobalToken $tokenName)
    {
    }
    public function getFilepath() : ?string
    {
        return null;
    }
    public function getToken() : TokenInterface
    {
        return $this->tokenName;
    }
}
