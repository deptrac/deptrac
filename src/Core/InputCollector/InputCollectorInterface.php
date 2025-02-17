<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\InputCollector;

interface InputCollectorInterface
{
    /**
     * @return list<string>
     *
     * @throws InputException
     */
    public function collect() : array;
}
