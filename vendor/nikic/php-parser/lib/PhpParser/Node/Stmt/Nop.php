<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\PhpParser\Node\Stmt;

use DEPTRAC_INTERNAL\PhpParser\Node;
/** Nop/empty statement (;). */
class Nop extends Node\Stmt
{
    public function getSubNodeNames() : array
    {
        return [];
    }
    public function getType() : string
    {
        return 'Stmt_Nop';
    }
}
