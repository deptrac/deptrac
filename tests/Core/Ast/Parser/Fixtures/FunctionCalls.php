<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

use function Some\Imported\imported;

final class FunctionCalls
{
    public function call(callable $callable): void
    {
        unqualified();
        \fullyQualified();
        Relative\qualified();
        namespace\relative();
        imported();
        $callable();
    }
}

function calls(): void
{
    unqualified();
}
