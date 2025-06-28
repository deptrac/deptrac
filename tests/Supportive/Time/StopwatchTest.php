<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Time;

use Deptrac\Deptrac\Supportive\Time\Stopwatch;
use Deptrac\Deptrac\Supportive\Time\StopwatchException;
use PHPUnit\Framework\TestCase;

class StopwatchTest extends TestCase
{
    private readonly Stopwatch $stopwatch;

    protected function setUp(): void
    {
        $this->stopwatch = new Stopwatch();
    }

    public function testEventCanNotBeStartedTwice(): void
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Period "test" is already started');

        $this->stopwatch->start('test');
        $this->stopwatch->start('test');
    }

    public function testEventCanNotBeStoppedWithoutBeingStarted(): void
    {
        $this->expectException(StopwatchException::class);
        $this->expectExceptionMessage('Period "test" is not started');

        $this->stopwatch->stop('test');
    }

    public function testEventFlowAndEventCanBeStartedAgain(): void
    {
        $this->stopwatch->start('test');
        $this->stopwatch->stop('test');
        $this->stopwatch->start('test');

        $this->expectNotToPerformAssertions();
    }
}
