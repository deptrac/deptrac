<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser\EventHandler;

use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Analyser\ViolationCreatingInterface;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
/**
 * @internal
 */
class DependsOnInternalToken implements ViolationCreatingInterface
{
    private ?string $internalTag;
    /**
     * @param array{internal_tag:string|null, ...} $config
     */
    public function __construct(private readonly EventHelper $eventHelper, array $config)
    {
        $this->internalTag = $config['internal_tag'];
    }
    public static function getSubscribedEvents()
    {
        return [ProcessEvent::class => ['invoke', -2]];
    }
    public function invoke(ProcessEvent $event) : void
    {
        $ruleset = $event->getResult();
        foreach ($event->dependentLayers as $dependentLayer => $_) {
            if ($event->dependerLayer !== $dependentLayer && $event->dependentReference instanceof ClassLikeReference) {
                $isInternal = $event->dependentReference->hasTag('@deptrac-internal');
                if (!$isInternal && null !== $this->internalTag) {
                    $isInternal = $event->dependentReference->hasTag($this->internalTag);
                }
                if ($isInternal) {
                    $this->eventHelper->addSkippableViolation($event, $ruleset, $dependentLayer, $this);
                    $event->stopPropagation();
                }
            }
        }
    }
    /**
     * @psalm-pure
     */
    public function ruleName() : string
    {
        return 'DependsOnInternalToken';
    }
    /**
     * @psalm-pure
     */
    public function ruleDescription() : string
    {
        return 'You are depending on a token that is internal to the layer and you are not part of that layer.';
    }
}
