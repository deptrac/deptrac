<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 *
 * Context of the dependency.
 *
 * Any additional info about where the dependency occurred.
 */
final readonly class DependencyContext
{
    public function __construct(
        public FileOccurrence $fileOccurrence,
        public DependencyType $dependencyType,
    ) {}
}
