<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType;
class ClassCollector extends \Qossmic\Deptrac\Core\Layer\Collector\AbstractTypeCollector
{
    protected function getType() : ClassLikeType
    {
        return ClassLikeType::TYPE_CLASS;
    }
}
