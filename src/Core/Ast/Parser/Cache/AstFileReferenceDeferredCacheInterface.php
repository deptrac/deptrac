<?php

namespace Deptrac\Deptrac\Core\Ast\Parser\Cache;

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;

interface AstFileReferenceDeferredCacheInterface extends AstFileReferenceCacheInterface
{
    public function load(): void;

    public function write(): void;
}
