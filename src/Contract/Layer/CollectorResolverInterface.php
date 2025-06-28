<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Layer;

interface CollectorResolverInterface
{
    /**
     * @param array<string, string|array<string, string>> $config
     */
    public function resolve(array $config): Collectable;
}
