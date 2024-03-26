<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PHPStan\PhpDocParser\Ast\Type;

use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ThisTypeNode implements TypeNode
{
    use NodeAttributes;
    public function __toString() : string
    {
        return '$this';
    }
}
