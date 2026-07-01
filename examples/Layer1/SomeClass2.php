<?php

namespace examples\Layer1;

use examples\Layer2\SomeOtherClass2;

class SomeClass2
{
    /**
     * @var SomeOtherClass2
     */
    private $someOtherClass2;

    /**
     * @param SomeOtherClass2 $someOtherClass2
     */
    public function __construct(SomeOtherClass2 $someOtherClass2)
    {
        $this->someOtherClass2 = $someOtherClass2;
    }
}
