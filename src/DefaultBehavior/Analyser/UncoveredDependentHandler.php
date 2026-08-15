<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Analyser;

use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use JetBrains\PHPStormStub\PhpStormStubsMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class UncoveredDependentHandler implements EventSubscriberInterface
{
    public function __construct(private bool $ignoreUncoveredInternalClasses) {}

    public function invoke(ProcessEvent $event): void
    {
        $dependent = $event->dependency->getDependent();
        $ruleset = $event->getResult();

        if ([] !== $event->dependentLayers) {
            return;
        }

        if ($dependent instanceof ClassLikeToken && !$this->ignoreUncoveredInternalClass($dependent)) {
            $ruleset->addRule(new Uncovered($event->dependency, $event->dependerLayer));
        }

        $event->stopPropagation();
    }

    private function ignoreUncoveredInternalClass(ClassLikeToken $token): bool
    {
        if (!$this->ignoreUncoveredInternalClasses) {
            return false;
        }

        $tokenString = $token->toString();

        return isset(PhpStormStubsMap::CLASSES[$tokenString]) || 'ReturnTypeWillChange' === $tokenString;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProcessEvent::class => ['invoke', 2],
        ];
    }
}
