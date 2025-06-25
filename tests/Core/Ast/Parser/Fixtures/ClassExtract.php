<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

#[\Attribute]
class ClassAttribute {}

final class ClassA
{
    #[ClassAttribute]
    private ClassB $classB;

    public function random()
    {
        try {
            throw new \Exception();
        } catch (\Throwable $t) {
            $t->getCode();
        }
    }
}

final class ClassB
{
}
