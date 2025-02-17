<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use Qossmic\Deptrac\Contract\Layer\CollectorInterface;
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
