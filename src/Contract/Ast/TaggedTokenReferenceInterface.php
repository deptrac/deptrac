<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Ast;

/**
 * Represents the AST-Token, its location, and associated tags.
 */
interface TaggedTokenReferenceInterface extends \Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface
{
    public function hasTag(string $name) : bool;
    /**
     * @return ?list<string>
     */
    public function getTagLines(string $name) : ?array;
}
