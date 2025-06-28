<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\DefaultBehavior\Layer\Helpers\AbstractTypeCollector;

final class ClassCollector extends AbstractTypeCollector
{
    protected function getType(): ClassLikeType
    {
        return ClassLikeType::TYPE_CLASS;
    }
}
