<?php 

namespace examples\MyNamespace\Controllers;

use examples\MyNamespace\Service\SomeService;

class SomeController
{
    /** SomeController constructor.*/
    public function __construct(SomeService $service)
    {
    }
}
