<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser\EventHandler;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Result\Allowed;
use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * @internal
 */
class AllowDependencyHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ProcessEvent::class => ['invoke', -100]];
    }
    public function invoke(ProcessEvent $event) : void
    {
        $ruleset = $event->getResult();
        foreach ($event->dependentLayers as $dependentLayer => $_) {
            $ruleset->addRule(new Allowed($event->dependency, $event->dependerLayer, $dependentLayer));
            $event->stopPropagation();
        }
    }
}
