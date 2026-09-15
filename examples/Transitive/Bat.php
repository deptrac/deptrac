<?php

namespace examples\Transitive;

class Bat
{
    public function __construct(Foo $foo, Bar $bar)
    {
    }
}
