<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Dependency;

use Qossmic\Deptrac\Contract\Ast\TokenInterface;
use Qossmic\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
class UnrecognizedTokenException extends RuntimeException implements ExceptionInterface
{
    public static function cannotCreateReference(TokenInterface $token) : self
    {
        return new self(\sprintf("Cannot create TokenReference for token '%s'", \get_debug_type($token)));
    }
}
