<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\AstMap\ClassLike;

use Qossmic\Deptrac\Contract\Ast\TokenInterface;
enum ClassLikeType : string implements \Qossmic\Deptrac\Contract\Ast\TokenInterface
{
    case TYPE_CLASSLIKE = 'classLike';
    case TYPE_CLASS = 'class';
    case TYPE_INTERFACE = 'interface';
    case TYPE_TRAIT = 'trait';
    public function toString() : string
    {
        return $this->value;
    }
}
