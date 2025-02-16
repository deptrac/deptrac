<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type;

use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstTypeNode implements TypeNode
{
    use NodeAttributes;
    /** @var ConstExprNode */
    public $constExpr;
    public function __construct(ConstExprNode $constExpr)
    {
        $this->constExpr = $constExpr;
    }
    public function __toString() : string
    {
        return $this->constExpr->__toString();
    }
}
