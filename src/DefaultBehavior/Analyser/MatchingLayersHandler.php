<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Analyser;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MatchingLayersHandler implements EventSubscriberInterface
{
    public function invoke(ProcessEvent $event): void
    {
        foreach ($event->dependentLayers as $dependeeLayer => $_) {
            if ($event->dependerLayer !== $dependeeLayer) {
                return;
            }
        }

        // For empty dependee layers see UncoveredDependeeHandler

        $event->stopPropagation();
    }

    public static function getSubscribedEvents()
    {
        return [
            ProcessEvent::class => ['invoke', 1],
        ];
    }
}
