<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

final readonly class AnalyserConfig
{
    /**
     * @param array<string, string> $types
     * @param array<string, string> $ignoredTypes
     */
    private function __construct(
        private array $types,
        private array $ignoredTypes,
        private ?string $internalTag,
    ) {}

    /** @param array<array-key,EmitterType|string> $types */
    public static function create(array $types = [], ?string $internalTag = null): self
    {
        return new self(
            types: self::createMap($types ?: [EmitterType::CLASS_TOKEN, EmitterType::FUNCTION_TOKEN]),
            ignoredTypes: [],
            internalTag: $internalTag
        );
    }

    /**
     * @param EmitterType|string ...$types a default emitter type or the key of a custom dependency emitter
     */
    public function withTypes(EmitterType|string ...$types): self
    {
        return new self(
            types: self::createMap($types),
            ignoredTypes: $this->ignoredTypes,
            internalTag: $this->internalTag
        );
    }

    /**
     * @param EmitterType|string ...$types types to ignore/disable
     */
    public function withIgnoredTypes(EmitterType|string ...$types): self
    {
        return new self(
            types: $this->types,
            ignoredTypes: self::createMap($types),
            internalTag: $this->internalTag
        );
    }

    public function withInternalTag(?string $tag): self
    {
        return new self(
            types: $this->types,
            ignoredTypes: $this->ignoredTypes,
            internalTag: $tag
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $result = [
            'types' => array_diff_key($this->types, $this->ignoredTypes),
            'internal_tag' => $this->internalTag,
        ];

        if (!empty($this->ignoredTypes)) {
            $result['ignored_types'] = $this->ignoredTypes;
        }

        return $result;
    }

    /**
     * @param array<array-key,EmitterType|string> $types
     *
     * @return array<string, string>
     */
    private static function createMap(array $types): array
    {
        /** @var array<string, string> $map */
        $map = array_reduce(
            $types,
            /**
             * @param array<string, string> $carry
             *
             * @return array<string, string>
             */
            static function (array $carry, EmitterType|string $type): array {
                $value = $type instanceof EmitterType ? $type->value : $type;
                $carry[$value] = $value;

                return $carry;
            },
            []
        );

        return $map;
    }
}
