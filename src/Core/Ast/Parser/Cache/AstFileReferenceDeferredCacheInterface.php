<?php

namespace Qossmic\Deptrac\Core\Ast\Parser\Cache;

interface AstFileReferenceDeferredCacheInterface extends \Qossmic\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceCacheInterface
{
    public function load() : void;
    public function write() : void;
}
