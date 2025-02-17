<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\File\Exception;

use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
class FileNotExistsException extends RuntimeException implements ExceptionInterface
{
    public static function fromFilePath(string $filepath) : self
    {
        return new self(\sprintf('"%s" is not a valid path or does not exists.', $filepath));
    }
}
