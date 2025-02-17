<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\Console\Command;

use Qossmic\Deptrac\Contract\ExceptionInterface;
use Qossmic\Deptrac\Core\Analyser\AnalyserException;
use RuntimeException;
final class CommandRunException extends RuntimeException implements ExceptionInterface
{
    public static function invalidFormatter() : self
    {
        return new self('Invalid output formatter selected.');
    }
    public static function finishedWithUncovered() : self
    {
        return new self('Analysis finished, but contains uncovered tokens.');
    }
    public static function finishedWithViolations() : self
    {
        return new self('Analysis finished, but contains ruleset violations.');
    }
    public static function failedWithErrors() : self
    {
        return new self('Analysis failed, due to an error.');
    }
    public static function analyserException(AnalyserException $e) : self
    {
        return new self('Analysis failed.', 0, $e);
    }
}
