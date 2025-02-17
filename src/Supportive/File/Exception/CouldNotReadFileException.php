<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\File\Exception;

use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
final class CouldNotReadFileException extends RuntimeException implements ExceptionInterface
{
    public static function fromFilename(string $filename) : self
    {
        return new self(\sprintf('File "%s" cannot be read.', $filename));
    }
}
