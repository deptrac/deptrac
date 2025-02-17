<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config;

final class AnalyserConfig
{
    /** @var array<string, EmitterType> */
    private array $types = [];
    /** @var ?string */
    private ?string $internalTag = null;
    private function __construct()
    {
    }
    /** @param ?array<array-key,EmitterType> $types */
    public static function create(?array $types = null, ?string $internalTag = null) : self
    {
        $analyser = new self();
        $types ??= [\Deptrac\Deptrac\Contract\Config\EmitterType::CLASS_TOKEN, \Deptrac\Deptrac\Contract\Config\EmitterType::FUNCTION_TOKEN];
        $analyser->types(...$types);
        $analyser->internalTag($internalTag);
        return $analyser;
    }
    public function types(\Deptrac\Deptrac\Contract\Config\EmitterType ...$types) : self
    {
        $this->types = [];
        foreach ($types as $type) {
            $this->types[$type->value] = $type;
        }
        return $this;
    }
    public function internalTag(?string $tag) : self
    {
        $this->internalTag = $tag;
        return $this;
    }
    /** @return array<string, mixed> */
    public function toArray() : array
    {
        return ['types' => \array_map(static fn(\Deptrac\Deptrac\Contract\Config\EmitterType $emitterType) => $emitterType->value, $this->types), 'internal_tag' => $this->internalTag];
    }
}
