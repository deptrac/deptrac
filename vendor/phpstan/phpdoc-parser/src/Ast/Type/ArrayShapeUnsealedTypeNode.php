<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type;

use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Node;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ArrayShapeUnsealedTypeNode implements Node
{
    use NodeAttributes;
    /** @var TypeNode */
    public $valueType;
    /** @var TypeNode|null */
    public $keyType;
    public function __construct(TypeNode $valueType, ?TypeNode $keyType)
    {
        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }
    public function __toString() : string
    {
        if ($this->keyType !== null) {
            return sprintf('<%s, %s>', $this->keyType, $this->valueType);
        }
        return sprintf('<%s>', $this->valueType);
    }
}
