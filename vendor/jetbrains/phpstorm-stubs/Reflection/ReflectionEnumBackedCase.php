<?php

namespace DEPTRAC_202404;

use DEPTRAC_202404\JetBrains\PhpStorm\Pure;
/**
 * @link https://php.net/manual/en/class.reflectionenumbackedcase.php
 * @since 8.1
 */
class ReflectionEnumBackedCase extends \ReflectionEnumUnitCase
{
    public function __construct(object|string $class, string $constant)
    {
    }
    #[Pure]
    public function getBackingValue() : int|string
    {
    }
}
/**
 * @link https://php.net/manual/en/class.reflectionenumbackedcase.php
 * @since 8.1
 */
\class_alias('DEPTRAC_202404\\ReflectionEnumBackedCase', 'ReflectionEnumBackedCase', \false);
