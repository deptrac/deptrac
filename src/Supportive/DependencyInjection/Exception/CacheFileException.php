<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\DependencyInjection\Exception;

use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use SplFileInfo;
final class CacheFileException extends RuntimeException implements ExceptionInterface
{
    public static function notWritable(SplFileInfo $cacheFile) : self
    {
        return new self(\sprintf('Cache file "%s" is not writable.', $cacheFile->getPathname()));
    }
}
