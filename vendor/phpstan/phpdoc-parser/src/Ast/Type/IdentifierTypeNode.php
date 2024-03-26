<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PHPStan\PhpDocParser\Ast\Type;

use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\NodeAttributes;
class IdentifierTypeNode implements TypeNode
{
    use NodeAttributes;
    /** @var string */
    public $name;
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    public function __toString() : string
    {
        return $this->name;
    }
}
