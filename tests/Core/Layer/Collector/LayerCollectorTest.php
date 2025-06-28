<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Contract\Layer\LayerResolverInterface;
use Deptrac\Deptrac\DefaultBehavior\Layer\LayerCollector;
use PHPUnit\Framework\TestCase;

final class LayerCollectorTest extends TestCase
{
    private LayerResolverInterface $resolver;
    private LayerCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = $this->createMock(LayerResolverInterface::class);

        $this->collector = new LayerCollector($this->resolver);
    }

    public function testConfig(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);
        $this->expectExceptionMessage('LayerCollector: Missing configuration');

        $this->collector->satisfy(
            [],
            new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo')),
        );
    }

    public function testSatisfyWithUnknownLayer(): void
    {
        $this->resolver
            ->expects($this->once())
            ->method('has')
            ->with('test')
            ->willReturn(false)
        ;

        $this->expectException(InvalidCollectorDefinitionException::class);
        $this->expectExceptionMessage('LayerCollector: Unknown layer "test" specified in collector.');

        $this->collector->satisfy(
            ['value' => 'test'],
            new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo')),
        );
    }

    public function testCircularReference(): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo'));
        $this->resolver
            ->method('has')
            ->with('FooLayer')
            ->willReturn(true)
        ;
        $this->resolver
            ->method('isReferenceInLayer')
            ->with('FooLayer', $reference)
            ->willReturnCallback(function (string $layerName, ClassLikeReference $reference) {
                return $this->collector->satisfy(['value' => 'FooLayer'], $reference);
            })
        ;

        $this->expectException(InvalidLayerDefinitionException::class);
        $this->expectExceptionMessage('LayerCollector: Circular dependency between layers detected. Token "App\Foo" could not be resolved.');

        $this->collector->satisfy(
            ['value' => 'FooLayer'],
            $reference,
        );
    }

    public function testSatisfyWhenReferenceIsInLayer(): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo'));
        $this->resolver
            ->method('has')
            ->with('AppLayer')
            ->willReturn(true)
        ;
        $this->resolver
            ->method('isReferenceInLayer')
            ->with('AppLayer', $reference)
            ->willReturn(true)
        ;

        $actual = $this->collector->satisfy(
            ['value' => 'AppLayer'],
            $reference,
        );

        self::assertTrue($actual);

        // test resolution caching by code coverage
        $actual = $this->collector->satisfy(
            ['value' => 'AppLayer'],
            $reference,
        );

        self::assertTrue($actual);
    }

    public function testSatisfyWhenReferenceIsNotInLayer(): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo'));
        $this->resolver
            ->method('has')
            ->with('AppLayer')
            ->willReturn(true)
        ;
        $this->resolver
            ->method('isReferenceInLayer')
            ->with('AppLayer', $reference)
            ->willReturn(false)
        ;

        $actual = $this->collector->satisfy(
            ['value' => 'AppLayer'],
            $reference,
        );

        self::assertFalse($actual);
    }
}
