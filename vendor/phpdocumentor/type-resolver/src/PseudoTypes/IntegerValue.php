<?php

/*
 * This file is part of phpDocumentor.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  @link      http://phpdoc.org
 *
 */
declare (strict_types=1);
namespace DEPTRAC_202404\phpDocumentor\Reflection\PseudoTypes;

use DEPTRAC_202404\phpDocumentor\Reflection\PseudoType;
use DEPTRAC_202404\phpDocumentor\Reflection\Type;
use DEPTRAC_202404\phpDocumentor\Reflection\Types\Integer;
/** @psalm-immutable */
final class IntegerValue implements PseudoType
{
    private int $value;
    public function __construct(int $value)
    {
        $this->value = $value;
    }
    public function getValue() : int
    {
        return $this->value;
    }
    public function underlyingType() : Type
    {
        return new Integer();
    }
    public function __toString() : string
    {
        return (string) $this->value;
    }
}
