<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

/**
 * @psalm-immutable
 *
 * Where in the file has the dependency occurred.
 */
final class FileOccurrence
{
    public function __construct(public readonly string $filepath, public readonly int $line)
    {
    }
}
