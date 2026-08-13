<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

#[\Attribute]
class GranularMethodAttribute {}

#[\Attribute]
class GranularClassAttribute {}

class GranularDependencyA {}

class GranularDependencyB {}

#[GranularClassAttribute]
class GranularFeature
{
    /**
     * @granular-tag handles registration
     */
    #[GranularMethodAttribute]
    public function handle(GranularDependencyA $a): void
    {
        $callback = function (): void {
            new GranularDependencyB();
        };
    }

    protected static function build(): ?GranularDependencyA
    {
        $anon = new class {
            public function anonMethod(): void
            {
                new GranularDependencyB();
            }
        };
    }

    private function audit(): void {}
}
