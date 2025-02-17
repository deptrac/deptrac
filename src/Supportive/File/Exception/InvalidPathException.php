<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\File\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use SplFileInfo;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
use function sprintf;
final class InvalidPathException extends RuntimeException implements ExceptionInterface
{
    public static function unreadablePath(SplFileInfo $path) : self
    {
        return new self(sprintf('Path "%s" is not a directory or is not readable.', Path::canonicalize($path->getPathname())));
    }
}
