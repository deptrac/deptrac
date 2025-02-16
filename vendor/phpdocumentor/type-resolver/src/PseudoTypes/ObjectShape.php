<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\phpDocumentor\Reflection\PseudoTypes;

use DEPTRAC_INTERNAL\phpDocumentor\Reflection\PseudoType;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Type;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Object_;
use function implode;
/** @psalm-immutable */
final class ObjectShape implements PseudoType
{
    /** @var ObjectShapeItem[] */
    private $items;
    public function __construct(ObjectShapeItem ...$items)
    {
        $this->items = $items;
    }
    /**
     * @return ObjectShapeItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }
    public function underlyingType() : Type
    {
        return new Object_();
    }
    public function __toString() : string
    {
        return 'object{' . implode(', ', $this->items) . '}';
    }
}
