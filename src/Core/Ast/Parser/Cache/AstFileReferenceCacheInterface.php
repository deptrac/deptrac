<?php

namespace Qossmic\Deptrac\Core\Ast\Parser\Cache;

use Qossmic\Deptrac\Core\Ast\AstMap\File\FileReference;
interface AstFileReferenceCacheInterface
{
    public function get(string $filepath) : ?FileReference;
    public function set(FileReference $fileReference) : void;
}
