<?php

declare (strict_types=1);
namespace DEPTRAC_202404\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\Node;
use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
class DoctrineAnnotation implements Node
{
    use NodeAttributes;
    /** @var string */
    public $name;
    /** @var list<DoctrineArgument> */
    public $arguments;
    /**
     * @param list<DoctrineArgument> $arguments
     */
    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
    public function __toString() : string
    {
        $arguments = implode(', ', $this->arguments);
        return $this->name . '(' . $arguments . ')';
    }
}
