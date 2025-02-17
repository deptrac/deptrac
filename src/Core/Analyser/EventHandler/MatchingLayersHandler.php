<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser\EventHandler;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * @internal
 */
class MatchingLayersHandler implements EventSubscriberInterface
{
    public function invoke(ProcessEvent $event) : void
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
        return [ProcessEvent::class => ['invoke', 1]];
    }
}
