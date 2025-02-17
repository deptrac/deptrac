<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
/**
 * @psalm-immutable
 */
class Collectable
{
    /**
     * @param array<string, bool|string|array<string, string>> $attributes
     */
    public function __construct(public readonly CollectorInterface $collector, public readonly array $attributes)
    {
    }
}
