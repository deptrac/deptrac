<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Result;

use Stringable;

/**
 * @psalm-immutable
 */
final readonly class Error implements Stringable
{
    public function __construct(private string $message) {}

    public function __toString(): string
    {
        return $this->message;
    }
}
