<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\File\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
class IOException extends RuntimeException implements ExceptionInterface
{
    public static function couldNotCopy(string $message) : self
    {
        return new self($message);
    }
}
