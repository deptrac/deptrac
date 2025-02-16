<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console;

class Env
{
    /**
     * @return string|false Environment variable value or false if the variable does not exist
     */
    public function get(string $name) : string|false
    {
        return \getenv($name);
    }
}
