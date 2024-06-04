<?php

namespace DEPTRAC_INTERNAL;

use DEPTRAC_INTERNAL\JetBrains\PhpStorm\Pure;
/**
 * @since 8.1
 */
class ReflectionIntersectionType extends \ReflectionType
{
    /** @return ReflectionType[] */
    #[Pure]
    public function getTypes() : array
    {
    }
}
/**
 * @since 8.1
 */
\class_alias('DEPTRAC_INTERNAL\\ReflectionIntersectionType', 'ReflectionIntersectionType', \false);
