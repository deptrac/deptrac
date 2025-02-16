<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PhpParser\Node\Stmt;

use DEPTRAC_INTERNAL\PhpParser\Node\StaticVar;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt;
class Static_ extends Stmt
{
    /** @var StaticVar[] Variable definitions */
    public array $vars;
    /**
     * Constructs a static variables list node.
     *
     * @param StaticVar[] $vars Variable definitions
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $vars, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->vars = $vars;
    }
    public function getSubNodeNames() : array
    {
        return ['vars'];
    }
    public function getType() : string
    {
        return 'Stmt_Static';
    }
}
