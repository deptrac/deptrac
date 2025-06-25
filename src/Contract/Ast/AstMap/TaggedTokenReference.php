<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * Helper trait for implementing TaggedTokenReferenceInterface.
 *
 * @psalm-immutable
 */
abstract class TaggedTokenReference implements TaggedTokenReferenceInterface
{
    /**
     * @param array<string,list<string>> $tags
     */
    protected function __construct(
        private readonly array $tags,
    ) {}

    public function hasTag(string $name): bool
    {
        return isset($this->tags[$name]);
    }

    /**
     * @return ?list<string>
     */
    public function getTagLines(string $name): ?array
    {
        return $this->tags[$name] ?? null;
    }
}
