<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers;

final class FormatterConfiguration
{
    /**
     * @param array<string, array<mixed>> $config
     */
    public function __construct(private readonly array $config) {}

    /**
     * @return array<mixed>
     */
    public function getConfigFor(string $area): array
    {
        return $this->config[$area] ?? [];
    }
}
