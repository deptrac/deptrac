<?php

namespace DEPTRAC_202404;

use DEPTRAC_202404\JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use DEPTRAC_202404\JetBrains\PhpStorm\Pure;
/**
 * @since 8.0
 */
class ReflectionUnionType extends \ReflectionType
{
    /**
     * Get list of types of union type
     *
     * @return ReflectionNamedType[]|ReflectionIntersectionType[]
     */
    #[Pure]
    #[LanguageLevelTypeAware(['8.2' => 'ReflectionNamedType[]|ReflectionIntersectionType[]'], default: 'ReflectionNamedType[]')]
    public function getTypes() : array
    {
    }
}
/**
 * @since 8.0
 */
\class_alias('DEPTRAC_202404\\ReflectionUnionType', 'ReflectionUnionType', \false);
