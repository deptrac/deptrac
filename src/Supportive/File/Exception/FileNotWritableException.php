<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\File\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use SplFileInfo;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
use function sprintf;
class FileNotWritableException extends RuntimeException implements ExceptionInterface
{
    public static function notWritable(SplFileInfo $file) : self
    {
        return new self(sprintf('Could not write file "%s".', Path::canonicalize($file->getPathname())));
    }
}
