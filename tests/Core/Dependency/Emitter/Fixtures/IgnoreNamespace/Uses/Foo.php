<?php

namespace IgnoreNamespace\Uses;

use IgnoreNamespace\Deps;
use IgnoreNamespace\Deps\UsedWithFQDN;
use IgnoreNamespace\Deps\Functions\functionUsedWithFQDN;

class Foo
{
    public function bar()
    {
        functionUsedWithFQDN();

        $someClass = new Deps\ClassUsedWithNamespace();
        Deps\functionUsedWithNamespace();
    }
}
