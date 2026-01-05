<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers;

final readonly class ConfigurationGraphViz
{
    /**
     * @param array{hidden_layers?: string[], groups?: array<string, string[]>, point_to_groups?: bool} $arr
     */
    public static function fromArray(array $arr): self
    {
        return new self($arr['hidden_layers'] ?? [], $arr['groups'] ?? [], $arr['point_to_groups'] ?? false);
    }

    /**
     * @param string[] $hiddenLayers
     * @param array<string, string[]> $groupsLayerMap
     */
    private function __construct(
        public array $hiddenLayers,
        public array $groupsLayerMap,
        public bool $pointToGroups,
    ) {}
}
