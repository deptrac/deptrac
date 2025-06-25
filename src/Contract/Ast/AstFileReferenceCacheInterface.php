<?php

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;

/**
 * Ast file cache to be used in custom parser implementation.
 *
 * @see ParserInterface
 */
interface AstFileReferenceCacheInterface
{
    public function get(string $filepath): ?FileReference;

    public function set(FileReference $fileReference): void;
}
