<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Analyser;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Result\Allowed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AllowDependencyHandler implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ProcessEvent::class => ['invoke', -100],
        ];
    }

    public function invoke(ProcessEvent $event): void
    {
        $ruleset = $event->getResult();

        foreach ($event->dependentLayers as $dependentLayer => $_) {
            $ruleset->addRule(new Allowed($event->dependency, $event->dependerLayer, $dependentLayer));
            $event->stopPropagation();
        }
    }
}
