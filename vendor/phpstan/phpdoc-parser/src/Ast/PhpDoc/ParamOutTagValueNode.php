<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PHPStan\PhpDocParser\Ast\PhpDoc;

use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\NodeAttributes;
use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class ParamOutTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /** @var TypeNode */
    public $type;
    /** @var string */
    public $parameterName;
    /** @var string (may be empty) */
    public $description;
    public function __construct(TypeNode $type, string $parameterName, string $description)
    {
        $this->type = $type;
        $this->parameterName = $parameterName;
        $this->description = $description;
    }
    public function __toString() : string
    {
        return trim("{$this->type} {$this->parameterName} {$this->description}");
    }
}
