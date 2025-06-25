<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\GithubActionsOutputFormatter;
use Deptrac\Deptrac\Supportive\Console\Command\AnalyseCommand;
use PHPUnit\Framework\TestCase;

class AnalyseCommandTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('GITHUB_ACTIONS=true');
        parent::setUp();
    }

    public function testDefaultFormatterForGithubActions(): void
    {
        self::assertSame(GithubActionsOutputFormatter::getName(), AnalyseCommand::getDefaultFormatter());
    }
}
