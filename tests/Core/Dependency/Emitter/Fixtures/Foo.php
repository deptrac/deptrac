<?php

namespace Foo;
use SomeUse;

final class Bar extends BarExtends implements BarInterface1, \BarInterface2 {

    use SomeTrait;

    public function foo(SomeParam $someParam, $lala): SomeClass
    {
        new SomeClass();
        new \SomeOtherClass();

        $foo = function(SomeOtherParam $someOtherParam) {

        };

        assert ($foo instanceof SomeInstanceOf);

        SomeClass::staticMethodCall();

        SomeClass::$staticPropertyAccess;
    }

    public function baz(): ?\Some\NamespacedClass {}

    public function foobar(): void
    {
        new class { public function foo(): SomeClass {} };

        function () : SomeClass {};

        function () : string {};

        function () : string2 {};

        function () : \string {};

        function () : ?string {};

        function () : ?SomeClass {};

        function () : self {};

        function () : ?self {};
    }

    public function bar(): void
    {
        $session = $_SESSION;
        $post = $_POST;
    }
}
