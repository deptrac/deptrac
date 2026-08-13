<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

class MethodTemplatesFixture
{
    /**
     * @template T of object
     *
     * @param T $value
     */
    public function withTemplate($value): void
    {
        /** @var T $copy */
        $copy = $value;
    }
}
