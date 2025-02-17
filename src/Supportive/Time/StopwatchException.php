<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Time;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use function sprintf;
final class StopwatchException extends RuntimeException implements ExceptionInterface
{
    public static function periodAlreadyStarted(string $period) : self
    {
        return new self(sprintf('Period "%s" is already started', $period));
    }
    public static function periodNotStarted(string $period) : self
    {
        return new self(sprintf('Period "%s" is not started', $period));
    }
}
