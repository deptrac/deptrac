<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\OutputFormatter;

use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
/**
 * Thrown when you are unable to provide output with your custom OutputFormatter.
 */
final class OutputException extends RuntimeException implements ExceptionInterface
{
    public static function withMessage(string $message) : self
    {
        return new self($message);
    }
}
