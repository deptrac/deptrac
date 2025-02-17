<?php

namespace Deptrac\Deptrac\Core\Ast\Parser\Cache;

interface AstFileReferenceDeferredCacheInterface extends \Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceCacheInterface
{
    public function load() : void;
    public function write() : void;
}
