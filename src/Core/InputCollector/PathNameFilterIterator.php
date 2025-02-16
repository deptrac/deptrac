<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\InputCollector;

use SplFileInfo;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
use DEPTRAC_INTERNAL\Symfony\Component\Finder\Iterator\PathFilterIterator;
use const DIRECTORY_SEPARATOR;
/**
 * @internal
 */
final class PathNameFilterIterator extends PathFilterIterator
{
    public function accept() : bool
    {
        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         *
         * @phpstan-var SplFileInfo $fileInfo
         */
        $fileInfo = $this->current();
        $filename = $this->isWindows() ? Path::normalize($fileInfo->getPathname()) : $fileInfo->getPathName();
        return $this->isAccepted($filename);
    }
    private function isWindows() : bool
    {
        return '\\' === DIRECTORY_SEPARATOR;
    }
}
