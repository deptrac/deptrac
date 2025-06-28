<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Analyser\EventHandler;

use Deptrac\Deptrac\Contract\Analyser\PostProcessEvent;
use Deptrac\Deptrac\DefaultBehavior\Analyser\UnmatchedSkippedViolations;
use PHPUnit\Framework\TestCase;

class UnmatchedSkippedViolationsTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = UnmatchedSkippedViolations::getSubscribedEvents();

        self::assertCount(1, $subscribedEvents);
        self::assertArrayHasKey(PostProcessEvent::class, $subscribedEvents);
        self::assertSame(['invoke'], $subscribedEvents[PostProcessEvent::class]);
    }
}
