<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config;

abstract class CollectorConfig
{
    protected bool $private = \false;
    protected \Deptrac\Deptrac\Contract\Config\CollectorType $collectorType;
    public function private() : self
    {
        $this->private = \true;
        return $this;
    }
    /** @return array{'type': string, 'private': bool, ...} */
    public function toArray() : array
    {
        return ['type' => $this->collectorType->value, 'private' => $this->private];
    }
}
