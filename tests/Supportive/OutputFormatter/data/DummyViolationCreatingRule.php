<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\OutputFormatter\data;

use Deptrac\Deptrac\Contract\Analyser\ViolationCreatingInterface;

class DummyViolationCreatingRule implements ViolationCreatingInterface
{
    public static function getSubscribedEvents()
    {
        return [];
    }

    public function ruleName(): string
    {
        return 'DummyRule';
    }

    public function ruleDescription(): string
    {
        return 'Why? Because!';
    }
}
