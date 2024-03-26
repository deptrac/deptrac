<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PHPStan\PhpDocParser\Ast\PhpDoc;

use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\NodeAttributes;
class PhpDocTextNode implements PhpDocChildNode
{
    use NodeAttributes;
    /** @var string */
    public $text;
    public function __construct(string $text)
    {
        $this->text = $text;
    }
    public function __toString() : string
    {
        return $this->text;
    }
}
