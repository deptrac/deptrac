<?php

namespace examples\Layer1;

use examples\Layer2\SomeOtherClass;

class AnotherClassLikeAController
{
    /**
     * @var SomeClass
     */
    private $someClass;

    /**
     * @var SomeOtherClass
     */
    private $someOtherClass;

    /**
     * @param SomeClass $someClass
     * @param SomeOtherClass $someOtherClass
     */
    public function __construct(SomeClass $someClass, SomeOtherClass $someOtherClass)
    {
        $this->someClass = $someClass;
        $this->someOtherClass = $someOtherClass;
    }
}
