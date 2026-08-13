<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

abstract class CollectorConfig
{
    protected bool $private = false;
    protected CollectorScope $scope = CollectorScope::TYPE_CLASS;
    protected CollectorType $collectorType;

    public function private(): self
    {
        $this->private = true;

        return $this;
    }

    /**
     * Apply this collector to class methods instead of whole tokens, assigning
     * matching methods to the layer on their own.
     */
    public function forMethods(): self
    {
        $this->scope = CollectorScope::TYPE_METHOD;

        return $this;
    }

    /** @return array{'type': string, 'private': bool, ...} */
    public function toArray(): array
    {
        return [
            'type' => $this->collectorType->value,
            'private' => $this->private,
        ] + $this->scopeToArray();
    }

    /** @return array{scope?: string} */
    final protected function scopeToArray(): array
    {
        return CollectorScope::TYPE_CLASS === $this->scope ? [] : ['scope' => $this->scope->value];
    }
}
