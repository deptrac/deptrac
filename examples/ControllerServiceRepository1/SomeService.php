<?php

namespace examples\MyNamespace\Service;

use examples\MyNamespace\Repository\SomeRepository;

class SomeService
{

    public function __construct(SomeRepository $someRepository)
    {
    }
}
