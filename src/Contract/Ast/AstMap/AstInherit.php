<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

use Stringable;

use function array_reverse;
use function implode;
use function sprintf;

/**
 * Represents forms of class inheritance within AST. Examples include:
 * - Class extending another class
 * - Class implementing an interface
 * - Class using a trait
 *
 * @psalm-immutable
 */
class AstInherit implements Stringable
{
    /**
     * @param AstInherit[] $path
     */
    public function __construct(
        public readonly TokenInterface $classLikeName,
        public readonly FileOccurrence $fileOccurrence,
        public readonly AstInheritType $type,
        private readonly array $path = [],
    ) {}

    /**
     * @return AstInherit[]
     */
    public function getPath(): array
    {
        return $this->path;
    }

    public function __toString(): string
    {
        $description = "{$this->classLikeName->toString()}::{$this->fileOccurrence->line} ({$this->type->value})";

        if ([] === $this->path) {
            return $description;
        }

        return sprintf('%s (path: %s)', $description, implode(' -> ', array_reverse($this->path)));
    }

    /**
     * @param AstInherit[] $path
     */
    public function replacePath(array $path): self
    {
        return new self($this->classLikeName, $this->fileOccurrence, $this->type, $path);
    }
}
