<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\AstMap\File;

use Deptrac\Deptrac\Contract\Ast\TokenInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
final class FileToken implements TokenInterface
{
    public readonly string $path;
    public function __construct(string $path)
    {
        $this->path = Path::normalize($path);
    }
    public function toString() : string
    {
        $wd = \getcwd();
        if (\false !== $wd) {
            $wd = Path::normalize($wd);
        }
        if (\false !== $wd && \str_starts_with($this->path, $wd)) {
            return \substr($this->path, \strlen($wd));
        }
        return $this->path;
    }
}
