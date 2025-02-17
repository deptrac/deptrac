<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType;
class TraitCollector extends \Deptrac\Deptrac\Core\Layer\Collector\AbstractTypeCollector
{
    protected function getType() : ClassLikeType
    {
        return ClassLikeType::TYPE_TRAIT;
    }
}
