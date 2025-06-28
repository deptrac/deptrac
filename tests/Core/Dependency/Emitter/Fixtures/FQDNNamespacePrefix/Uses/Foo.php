<?php

namespace FQDNNamespacePrefix\Uses;

use FQDNNamespacePrefix\FQDN;

class Foo
{
    public function bar()
    {
        $fqdn = new FQDN();
        $someClass = new FQDN\SomeClass();
    }
}
