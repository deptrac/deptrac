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
namespace DEPTRAC_INTERNAL\phpDocumentor\Reflection\PseudoTypes;

use DEPTRAC_INTERNAL\phpDocumentor\Reflection\PseudoType;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Type;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Array_;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Integer;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Mixed_;
/**
 * Value Object representing the type 'non-empty-list'.
 *
 * @psalm-immutable
 */
final class NonEmptyList extends Array_ implements PseudoType
{
    public function underlyingType() : Type
    {
        return new Array_();
    }
    public function __construct(?Type $valueType = null)
    {
        parent::__construct($valueType, new Integer());
    }
    /**
     * Returns a rendered output of the Type as it would be used in a DocBlock.
     */
    public function __toString() : string
    {
        if ($this->valueType instanceof Mixed_) {
            return 'non-empty-list';
        }
        return 'non-empty-list<' . $this->valueType . '>';
    }
}
