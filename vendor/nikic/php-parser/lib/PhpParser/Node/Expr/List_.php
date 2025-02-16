<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PhpParser\Node\Expr;

use DEPTRAC_INTERNAL\PhpParser\Node\ArrayItem;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr;
class List_ extends Expr
{
    // For use in "kind" attribute
    public const KIND_LIST = 1;
    // list() syntax
    public const KIND_ARRAY = 2;
    // [] syntax
    /** @var (ArrayItem|null)[] List of items to assign to */
    public array $items;
    /**
     * Constructs a list() destructuring node.
     *
     * @param (ArrayItem|null)[] $items List of items to assign to
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $items, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->items = $items;
    }
    public function getSubNodeNames() : array
    {
        return ['items'];
    }
    public function getType() : string
    {
        return 'Expr_List';
    }
}
