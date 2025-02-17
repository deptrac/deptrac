<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser\Cache;

use Qossmic\Deptrac\Core\Ast\AstMap\File\FileReference;
final class AstFileReferenceInMemoryCache implements \Qossmic\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceCacheInterface
{
    /**
     * @var array<string, FileReference>
     */
    private array $cache = [];
    public function get(string $filepath) : ?FileReference
    {
        $filepath = \realpath($filepath);
        return $this->cache[$filepath] ?? null;
    }
    public function set(FileReference $fileReference) : void
    {
        $filepath = (string) \realpath($fileReference->filepath);
        $this->cache[$filepath] = $fileReference;
    }
}
