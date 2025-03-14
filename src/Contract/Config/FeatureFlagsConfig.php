<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

final class FeatureFlagsConfig
{
    private bool $phpstanParser = false;

    private function __construct() {}

    public static function create(bool $phpstanParser = false): self
    {
        $featureFlags = new self();

        $featureFlags->phpstanParser($phpstanParser);

        return $featureFlags;
    }

    public function phpstanParser(bool $phpstanParser): self
    {
        $this->phpstanParser = $phpstanParser;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'phpstan_parser' => $this->phpstanParser,
        ];
    }
}
