<?php

namespace examples\Transitive;

class Baz
{
    public function __construct(Foo $foo, Bar $bar)
    {
    }
}
