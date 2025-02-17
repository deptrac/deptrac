<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\AstMap;

use Deptrac\Deptrac\Contract\Ast\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\TokenInterface;
/**
 * @psalm-immutable
 */
class DependencyToken
{
    public function __construct(public readonly TokenInterface $token, public readonly DependencyContext $context)
    {
    }
}
