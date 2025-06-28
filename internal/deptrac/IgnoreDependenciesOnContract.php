<?php

declare(strict_types=1);

namespace Internal\Deptrac\Deptrac;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IgnoreDependenciesOnContract implements EventSubscriberInterface
{
    /**
     * @api
     */
    public function onProcessEvent(ProcessEvent $event): void
    {
        if (array_key_exists('Contract', $event->dependentLayers)) {
            $event->stopPropagation();
        }
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProcessEvent::class => 'onProcessEvent',
        ];
    }
}
