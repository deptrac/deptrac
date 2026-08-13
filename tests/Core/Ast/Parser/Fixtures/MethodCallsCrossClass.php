<?php

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

class CrossClassTarget
{
    public function targetMethod(): void {}

    public static function create(): CrossClassTarget
    {
        return new CrossClassTarget();
    }
}

class CrossClassCaller
{
    private CrossClassTarget $target;

    public function callViaProperty(): void
    {
        $this->target->targetMethod();
    }

    public function callViaNew(): void
    {
        (new CrossClassTarget())->targetMethod();
    }

    public function callViaStaticFactory(): void
    {
        CrossClassTarget::create()->targetMethod();
    }

    public function callViaParameter(CrossClassTarget $param): void
    {
        $param->targetMethod();
    }
}
