<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use Throwable;

class AstException extends RuntimeException implements ExceptionInterface
{
    public static function couldNotCollectFiles(Throwable $exception): self
    {
        return new self('Could not create AstMap.', 0, $exception);
    }
}
