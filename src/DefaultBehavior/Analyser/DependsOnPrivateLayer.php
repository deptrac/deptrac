<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Analyser;

use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Analyser\ViolationCreatingInterface;

final readonly class DependsOnPrivateLayer implements ViolationCreatingInterface
{
    public function __construct(private EventHelper $eventHelper) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProcessEvent::class => ['invoke', -3],
        ];
    }

    public function invoke(ProcessEvent $event): void
    {
        $ruleset = $event->getResult();

        foreach ($event->dependentLayers as $dependentLayer => $isPublic) {
            if ($event->dependerLayer !== $dependentLayer && !$isPublic) {
                $this->eventHelper->addSkippableViolation($event, $ruleset, $dependentLayer, $this);
                $event->stopPropagation();
            }
        }
    }

    /**
     * @psalm-pure
     */
    public function ruleName(): string
    {
        return 'DependsOnPrivateLayer';
    }

    /**
     * @psalm-pure
     */
    public function ruleDescription(): string
    {
        return 'You are depending on a part of a layer that was defined as private to that layer and you are not part of that layer.';
    }
}
