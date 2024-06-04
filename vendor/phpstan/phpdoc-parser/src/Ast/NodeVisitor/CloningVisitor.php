<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\NodeVisitor;

use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Attribute;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Node;
final class CloningVisitor extends AbstractNodeVisitor
{
    public function enterNode(Node $originalNode)
    {
        $node = clone $originalNode;
        $node->setAttribute(Attribute::ORIGINAL_NODE, $originalNode);
        return $node;
    }
}
