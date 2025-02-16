<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\File\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use SplFileInfo;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
use function sprintf;
final class FileAlreadyExistsException extends RuntimeException implements ExceptionInterface
{
    public static function alreadyExists(SplFileInfo $file) : self
    {
        return new self(sprintf('A file named "%s" already exists.', Path::canonicalize($file->getPathname())));
    }
}
