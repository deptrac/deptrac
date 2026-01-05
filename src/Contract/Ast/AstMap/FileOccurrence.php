<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 *
 * Where in the file has the dependency occurred.
 */
final readonly class FileOccurrence
{
    public function __construct(
        public string $filepath,
        public int $line,
    ) {}
}
