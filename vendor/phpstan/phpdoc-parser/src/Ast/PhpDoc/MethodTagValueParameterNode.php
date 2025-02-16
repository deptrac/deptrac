<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc;

use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Node;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\NodeAttributes;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\TypeNode;
class MethodTagValueParameterNode implements Node
{
    use NodeAttributes;
    /** @var TypeNode|null */
    public $type;
    /** @var bool */
    public $isReference;
    /** @var bool */
    public $isVariadic;
    /** @var string */
    public $parameterName;
    /** @var ConstExprNode|null */
    public $defaultValue;
    public function __construct(?TypeNode $type, bool $isReference, bool $isVariadic, string $parameterName, ?ConstExprNode $defaultValue)
    {
        $this->type = $type;
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->parameterName = $parameterName;
        $this->defaultValue = $defaultValue;
    }
    public function __toString() : string
    {
        $type = $this->type !== null ? "{$this->type} " : '';
        $isReference = $this->isReference ? '&' : '';
        $isVariadic = $this->isVariadic ? '...' : '';
        $default = $this->defaultValue !== null ? " = {$this->defaultValue}" : '';
        return "{$type}{$isReference}{$isVariadic}{$this->parameterName}{$default}";
    }
}
