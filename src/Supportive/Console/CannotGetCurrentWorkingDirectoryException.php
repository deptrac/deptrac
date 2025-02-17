<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
final class CannotGetCurrentWorkingDirectoryException extends RuntimeException implements ExceptionInterface
{
    public function __construct(string $message = 'Internal error.')
    {
        parent::__construct($message);
    }
    public static function cannotGetCWD() : self
    {
        return new self('Could not get current working directory. Check `getcwd()` internal PHP function for details.');
    }
}
