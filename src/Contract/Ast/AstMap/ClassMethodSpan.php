<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * Describes where a method is declared within a class-like reference.
 *
 * The span covers the whole method declaration including its attribute
 * groups, so any dependency recorded on the class-like reference whose line
 * falls inside the span originates from that method.
 *
 * @psalm-immutable
 */
final class ClassMethodSpan
{
    public function __construct(
        public readonly string $name,
        public readonly int $startLine,
        public readonly int $endLine,
        public readonly ClassMethodVisibility $visibility,
        public readonly bool $isStatic,
    ) {}
}
