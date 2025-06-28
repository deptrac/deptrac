<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Analyser\EventHandler;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\DefaultBehavior\Analyser\MatchingLayersHandler;
use PHPUnit\Framework\TestCase;

class MatchingLayersHandlerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = MatchingLayersHandler::getSubscribedEvents();

        self::assertCount(1, $subscribedEvents);
        self::assertArrayHasKey(ProcessEvent::class, $subscribedEvents);
        self::assertSame(['invoke', 1], $subscribedEvents[ProcessEvent::class]);
    }
}
