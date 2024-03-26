<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\Node;
use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class DoctrineArray implements Node
{
    use NodeAttributes;
    /** @var list<DoctrineArrayItem> */
    public $items;
    /**
     * @param list<DoctrineArrayItem> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }
    public function __toString() : string
    {
        $items = implode(', ', $this->items);
        return '{' . $items . '}';
    }
}
