<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\InputCollector;

use Exception;
use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
class InputException extends RuntimeException implements ExceptionInterface
{
    public static function couldNotCollectFiles(Exception $exception) : self
    {
        return new self('Could not collect files.', 0, $exception);
    }
}
