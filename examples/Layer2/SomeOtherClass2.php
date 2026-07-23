<?php

namespace examples\Layer2;

use examples\Layer1\SomeClass2;

class SomeOtherClass2
{
    /**
     * @var SomeClass2
     */
    private $someClass2;

    /**
     * @param SomeClass2 $someClass2
     */
    public function __construct(SomeClass2 $someClass2)
    {
        $this->someClass2 = $someClass2;
    }
}
