<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Config;

abstract class CollectorConfig
{
    protected bool $private = \false;
    protected \Qossmic\Deptrac\Contract\Config\CollectorType $collectorType;
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
