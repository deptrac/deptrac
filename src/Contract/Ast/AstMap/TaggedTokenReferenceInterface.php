<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * Represents the AST-Token, its location, and associated tags.
 */
interface TaggedTokenReferenceInterface extends TokenReferenceInterface
{
    public function hasTag(string $name): bool;

    /**
     * @return ?list<string>
     */
    public function getTagLines(string $name): ?array;
}
