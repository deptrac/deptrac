<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

/**
 * @psalm-immutable
 */
final class FunctionReference extends TaggedTokenReference
{
    /**
     * @param DependencyToken[] $dependencies
     * @param array<string,list<string>> $tags
     */
    public function __construct(
        private readonly FunctionToken $functionName,
        public readonly array $dependencies = [],
        public readonly array $tags = [],
        private readonly ?FileReference $fileReference = null,
    ) {
        parent::__construct($tags);
    }

    public function withFileReference(FileReference $astFileReference): self
    {
        return new self(
            $this->functionName,
            $this->dependencies,
            $this->tags,
            $astFileReference
        );
    }

    public function getFilepath(): ?string
    {
        return $this->fileReference?->filepath;
    }

    public function getToken(): TokenInterface
    {
        return $this->functionName;
    }
}
