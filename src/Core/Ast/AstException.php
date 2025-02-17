<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use Deptrac\Deptrac\Core\InputCollector\InputException;
use RuntimeException;
class AstException extends RuntimeException implements ExceptionInterface
{
    public static function couldNotCollectFiles(InputException $exception) : self
    {
        return new self('Could not create AstMap.', 0, $exception);
    }
}
