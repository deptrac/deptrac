<?php

namespace Deptrac\Deptrac\Core\Ast\Parser\Cache;

use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
interface AstFileReferenceCacheInterface
{
    public function get(string $filepath) : ?FileReference;
    public function set(FileReference $fileReference) : void;
}
