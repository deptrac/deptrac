<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Analyser;

use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Analyser\PostProcessEvent;
use Deptrac\Deptrac\Contract\Result\Error;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function sprintf;

final class UnmatchedSkippedViolations implements EventSubscriberInterface
{
    public function __construct(private readonly EventHelper $eventHelper) {}

    public function invoke(PostProcessEvent $event): void
    {
        $ruleset = $event->getResult();

        foreach ($this->eventHelper->unmatchedSkippedViolations() as $tokenA => $tokensB) {
            foreach ($tokensB as $tokenB) {
                $ruleset->addError(new Error(sprintf('Skipped violation "%s" for "%s" was not matched.', $tokenB, $tokenA)));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            PostProcessEvent::class => ['invoke'],
        ];
    }
}
