<?php

declare(strict_types = 1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\NikicPhpParser\Fixtures;

use Tests\Deptrac\Deptrac\AstRunner\AstParser\NikicPhpParser\Fixtures\Tests;

/**
 * @template Ta of AnotherThing
 */
class Thing
{
    /**
     * @template Tb of string
     */
    public function method(): void
    {
        /**
         * @var Tb $var
         */
        $var = '';
    }
}

class AnotherThing
{
}
