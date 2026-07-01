<?php

namespace examples\Layer2;

use examples\Layer1\SomeClass;

class SomeOtherClass
{
    /**
     * @var SomeClass
     */
    private $someClass;

    /**
     * @param SomeClass $someClass
     */
    public function __construct(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }
}
