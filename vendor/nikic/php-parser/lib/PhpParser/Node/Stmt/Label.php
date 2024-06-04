<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PhpParser\Node\Stmt;

use DEPTRAC_INTERNAL\PhpParser\Node\Identifier;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt;
class Label extends Stmt
{
    /** @var Identifier Name */
    public $name;
    /**
     * Constructs a label node.
     *
     * @param string|Identifier $name       Name
     * @param array             $attributes Additional attributes
     */
    public function __construct($name, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->name = \is_string($name) ? new Identifier($name) : $name;
    }
    public function getSubNodeNames() : array
    {
        return ['name'];
    }
    public function getType() : string
    {
        return 'Stmt_Label';
    }
}
