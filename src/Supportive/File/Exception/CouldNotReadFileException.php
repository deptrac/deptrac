<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\File\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
final class CouldNotReadFileException extends RuntimeException implements ExceptionInterface
{
    public static function fromFilename(string $filename) : self
    {
        return new self(\sprintf('File "%s" cannot be read.', $filename));
    }
}
