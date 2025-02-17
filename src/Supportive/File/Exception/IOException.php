<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\File\Exception;

use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
class IOException extends RuntimeException implements ExceptionInterface
{
    public static function couldNotCopy(string $message) : self
    {
        return new self($message);
    }
}
