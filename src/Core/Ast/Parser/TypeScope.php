<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser;

class TypeScope
{
    /**
     * @var array<string, string> alias => className
     */
    private array $uses = [];
    public function __construct(public readonly string $namespace)
    {
    }
    public function addUse(string $className, ?string $alias) : void
    {
        $this->uses[$alias ?: $className] = $className;
    }
    /**
     * @return array<string, string>
     */
    public function getUses() : array
    {
        return $this->uses;
    }
}
