<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorConfig;
use Deptrac\Deptrac\Contract\Config\CollectorType;

final class InternalIOConfig extends CollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_INTERNAL_IO;

    private function __construct(
        private ?string $exclude,
    ) {}

    /**
     * @param ?string $exclude regular expression for the names of functions
     *                         and classes that should not be collected
     */
    public static function create(?string $exclude = null): self
    {
        return new self($exclude);
    }

    public function exclude(string $regexpr): self
    {
        $this->exclude = $regexpr;

        return $this;
    }

    public function toArray(): array
    {
        $config = parent::toArray();

        if (null !== $this->exclude) {
            $config['exclude'] = $this->exclude;
        }

        return $config;
    }
}
