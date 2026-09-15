<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

final class AnalyserConfig
{
    /** @var array<string, string> */
    private array $types = [];

    private ?string $internalTag = null;

    private function __construct() {}

    /** @param ?array<array-key,EmitterType|string> $types */
    public static function create(?array $types = null, ?string $internalTag = null): self
    {
        $analyser = new self();

        $types ??= [EmitterType::CLASS_TOKEN, EmitterType::FUNCTION_TOKEN];
        $analyser->types(...$types);

        $analyser->internalTag($internalTag);

        return $analyser;
    }

    /**
     * @param EmitterType|string ...$types a default emitter type or the key
     *                                     of a custom dependency emitter
     */
    public function types(EmitterType|string ...$types): self
    {
        $this->types = [];
        foreach ($types as $type) {
            $value = $type instanceof EmitterType ? $type->value : $type;
            $this->types[$value] = $value;
        }

        return $this;
    }

    public function internalTag(?string $tag): self
    {
        $this->internalTag = $tag;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'types' => $this->types,
            'internal_tag' => $this->internalTag,
        ];
    }
}
