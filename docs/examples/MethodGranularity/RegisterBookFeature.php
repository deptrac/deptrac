<?php

namespace Examples\MethodGranularity;

#[\Attribute]
class AsController {}

#[\Attribute]
class AsEventListener {}

class BookCommand {}

#[AsController]
class RegisterBookFeature
{
    public function registerBook(): void
    {
        $this->handleRegister(new BookCommand());
    }

    #[AsEventListener]
    public function handleRegister(BookCommand $command): void
    {
        // violation: the Application layer must not depend on Infrastructure
        $this->registerBook();
    }
}
