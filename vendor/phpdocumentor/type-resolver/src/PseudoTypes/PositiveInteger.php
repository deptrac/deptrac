<?php

declare (strict_types=1);
/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link      http://phpdoc.org
 */
namespace DEPTRAC_202404\phpDocumentor\Reflection\PseudoTypes;

use DEPTRAC_202404\phpDocumentor\Reflection\PseudoType;
use DEPTRAC_202404\phpDocumentor\Reflection\Type;
use DEPTRAC_202404\phpDocumentor\Reflection\Types\Integer;
/**
 * Value Object representing the type 'int'.
 *
 * @psalm-immutable
 */
final class PositiveInteger extends Integer implements PseudoType
{
    public function underlyingType() : Type
    {
        return new Integer();
    }
    /**
     * Returns a rendered output of the Type as it would be used in a DocBlock.
     */
    public function __toString() : string
    {
        return 'positive-int';
    }
}
