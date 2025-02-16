<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Result;

use Stringable;
/**
 * @psalm-immutable
 */
final class Error implements Stringable
{
    public function __construct(private readonly string $message)
    {
    }
    public function __toString() : string
    {
        return $this->message;
    }
}
