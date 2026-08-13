<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 */
final class ClassMethodReference extends TaggedTokenReference
{
    /**
     * @param FileOccurrence|null $declaration null for methods deptrac has not parsed (e.g. on vendor classes)
     * @param DependencyToken[] $dependencies dependencies declared inside the method (signature, attributes and body);
     *                                        they are also part of the owning ClassLikeReference's dependencies
     * @param array<string,list<string>> $tags
     */
    public function __construct(
        private readonly ClassMethodToken $methodToken,
        public readonly ClassMethodVisibility $visibility,
        public readonly bool $isStatic,
        public readonly ?FileOccurrence $declaration = null,
        public readonly array $dependencies = [],
        public readonly array $tags = [],
    ) {
        parent::__construct($tags);
    }

    public function getFilepath(): ?string
    {
        return $this->declaration?->filepath;
    }

    public function getToken(): ClassMethodToken
    {
        return $this->methodToken;
    }
}
