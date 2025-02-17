<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

interface CollectorResolverInterface
{
    /**
     * @param array<string, string|array<string, string>> $config
     */
    public function resolve(array $config) : \Deptrac\Deptrac\Core\Layer\Collector\Collectable;
}
