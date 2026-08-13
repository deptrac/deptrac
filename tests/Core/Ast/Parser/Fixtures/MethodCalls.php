<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

class MethodCallsBase
{
    protected function inherited(): void {}
}

class MethodCallsFixture extends MethodCallsBase
{
    public function caller(): void
    {
        $this->target();
        self::staticTarget();
        static::staticTarget();
        parent::inherited();
        $callable = $this->target(...);
        $name = 'target';
        $this->$name();
        $closure = function (): void {
            $this->target();
        };
    }

    private function target(): void {}

    public static function staticTarget(): void {}
}

function methodCallsHelper(MethodCallsFixture $fixture): void
{
    $fixture->target();
}
