<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Analyser\EventHandler;

use Deptrac\Deptrac\Contract\Analyser\AnalysisResult;
use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Core\Layer\LayerProvider;
use Deptrac\Deptrac\DefaultBehavior\Analyser\DependsOnPrivateLayer;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use Deptrac\Deptrac\Supportive\OutputFormatter\YamlBaselineMapper;
use PHPUnit\Framework\TestCase;

final class DependsOnPrivateLayerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = DependsOnPrivateLayer::getSubscribedEvents();

        self::assertCount(1, $subscribedEvents);
        self::assertArrayHasKey(ProcessEvent::class, $subscribedEvents);
        self::assertSame(['invoke', -3], $subscribedEvents[ProcessEvent::class]);
    }

    private function makeEvent(
        string $dependerLayer, string $dependentLayer, bool $isPublic,
    ): ProcessEvent {
        $dependerToken = ClassLikeToken::fromFQCN('DependerClass');
        $dependentToken = ClassLikeToken::fromFQCN('DependentClass');

        return new ProcessEvent(
            new Dependency(
                $dependerToken,
                $dependentToken,
                new DependencyContext(new FileOccurrence('test', 1), DependencyType::STATIC_METHOD)
            ),
            new ClassLikeReference($dependerToken, ClassLikeType::TYPE_CLASS, [], [], []),
            $dependerLayer,
            new ClassLikeReference($dependentToken, ClassLikeType::TYPE_CLASS, [], [], []),
            [$dependentLayer => $isPublic],
            new AnalysisResult()
        );
    }

    public function testNoViolationsWhenDependentLayerIsPublic(): void
    {
        $helper = new EventHelper(new LayerProvider([]), new YamlBaselineMapper([]));
        $handler = new DependsOnPrivateLayer($helper);

        $event = $this->makeEvent('DependerLayer', 'DependentLayer', true);
        $handler->invoke($event);

        $this->assertCount(
            0,
            $event->getResult()->rules(),
            'No violations should be added when dependent layer is public'
        );

        $this->assertFalse(
            $event->isPropagationStopped(),
            'Propagation should continue if dependent layer is public'
        );
    }

    public function testPropagationContinuesWhenPrivateLayerDependsOnItself(): void
    {
        $helper = new EventHelper(new LayerProvider([]), new YamlBaselineMapper([]));
        $handler = new DependsOnPrivateLayer($helper);

        $event = $this->makeEvent('LayerA', 'LayerA', false);
        $handler->invoke($event);

        $this->assertCount(
            0,
            $event->getResult()->rules(),
            'No violations should be added when private layer depends on itself'
        );

        $this->assertFalse(
            $event->isPropagationStopped(),
            'Propagation should continue if private layer depends on itself'
        );
    }

    public function testPropagationContinuesWhenPublicLayerDependsOnItself(): void
    {
        $helper = new EventHelper(new LayerProvider([]), new YamlBaselineMapper([]));
        $handler = new DependsOnPrivateLayer($helper);

        $event = $this->makeEvent('layerA', 'layerA', true);
        $handler->invoke($event);

        $this->assertCount(
            0,
            $event->getResult()->rules(),
            'No violations should be added when public layer depends on itself'
        );

        $this->assertFalse(
            $event->isPropagationStopped(),
            'Propagation should continue if public layer depends on itself'
        );
    }

    public function testPropagationStoppedWhenDependingOnPrivateLayer(): void
    {
        $helper = new EventHelper(new LayerProvider([]), new YamlBaselineMapper([]));
        $handler = new DependsOnPrivateLayer($helper);

        $event = $this->makeEvent('DependerLayer', 'DependentLayer', false);
        $handler->invoke($event);

        $violations = $event->getResult()->rules()[Violation::class] ?? [];
        $this->assertCount(
            1,
            $violations,
            'Violation should be added when depending on private layer'
        );

        $rule = array_values($violations)[0];
        $this->assertSame(
            'DependerLayer',
            $rule->getDependerLayer(),
        );
        $this->assertSame(
            'DependentLayer',
            $rule->getDependentLayer(),
        );
        $this->assertSame(
            'DependsOnPrivateLayer',
            $rule->ruleName(),
        );

        $this->assertTrue(
            $event->isPropagationStopped(),
            'Propagation should stop if depending on private layer'
        );
    }
}
