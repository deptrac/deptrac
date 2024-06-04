<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc;

use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\NodeAttributes;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class TemplateTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /** @var string */
    public $name;
    /** @var TypeNode|null */
    public $bound;
    /** @var TypeNode|null */
    public $default;
    /** @var string (may be empty) */
    public $description;
    public function __construct(string $name, ?TypeNode $bound, string $description, ?TypeNode $default = null)
    {
        $this->name = $name;
        $this->bound = $bound;
        $this->default = $default;
        $this->description = $description;
    }
    public function __toString() : string
    {
        $bound = $this->bound !== null ? " of {$this->bound}" : '';
        $default = $this->default !== null ? " = {$this->default}" : '';
        return trim("{$this->name}{$bound}{$default} {$this->description}");
    }
}
