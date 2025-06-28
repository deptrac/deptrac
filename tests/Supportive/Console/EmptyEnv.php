<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\Console\Env;

final class EmptyEnv extends Env
{
    public function get(string $envName)
    {
        return false;
    }
}
