<?php

namespace Foo;

class MethodCallClass
{
    public function a(): void
    {
        $this->b();
        self::c();
        new SomeClass();
    }

    private function b(): void {}

    public static function c(): void {}
}
