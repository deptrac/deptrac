<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Layer;

/**
 * @psalm-immutable
 */
final readonly class Collectable
{
    /**
     * @param array<string, bool|string|array<string, string>> $attributes
     */
    public function __construct(
        public CollectorInterface $collector,
        public array $attributes,
    ) {}
}
