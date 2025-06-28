<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

#[\Attribute]
class MethodAttribute {}
class MethodSignaturesA
{
    #[MethodAttribute]
    public function foo() {}
}

class MethodSignaturesB
{
    public function getA(): ?MethodSignaturesA
    {
        // no-op
        return null;
    }
}

class MethodSignaturesC
{
    public function test( #[\SensitiveParameter] MethodSignaturesB $b )
    {
        $a = $b->getA();

        // Not tracked yet:
        $a->foo();
    }

}

