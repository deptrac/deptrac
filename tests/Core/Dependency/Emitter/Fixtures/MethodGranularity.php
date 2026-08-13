<?php

namespace Foo;

class MethodEmitterClass
{
    public function layered(SomeParam $p): void
    {
        $this->plain();
        new SomeClass();
    }

    public function plain(): void
    {
        new OtherClass();
    }
}
