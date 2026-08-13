<?php

namespace Tests\Deptrac\Deptrac\Core\Analyser\Fixtures;

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
        $this->registerBook();
    }
}
