<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc;

use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\NodeAttributes;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineTagValueNode;
use function trim;
class PhpDocTagNode implements PhpDocChildNode
{
    use NodeAttributes;
    /** @var string */
    public $name;
    /** @var PhpDocTagValueNode */
    public $value;
    public function __construct(string $name, PhpDocTagValueNode $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    public function __toString() : string
    {
        if ($this->value instanceof DoctrineTagValueNode) {
            return (string) $this->value;
        }
        return trim("{$this->name} {$this->value}");
    }
}
