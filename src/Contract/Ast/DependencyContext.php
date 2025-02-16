<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Ast;

/**
 * @psalm-immutable
 *
 * Context of the dependency.
 *
 * Any additional info about where the dependency occurred.
 */
final class DependencyContext
{
    public function __construct(public readonly \Deptrac\Deptrac\Contract\Ast\FileOccurrence $fileOccurrence, public readonly \Deptrac\Deptrac\Contract\Ast\DependencyType $dependencyType)
    {
    }
}
