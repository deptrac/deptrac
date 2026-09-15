<?php

namespace Core;

class CoreClass
{
    public function __construct()
    {
        new \Other\OtherClass();
    }
}

namespace Other;

class OtherClass {}
