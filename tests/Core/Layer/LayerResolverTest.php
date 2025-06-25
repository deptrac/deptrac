<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Layer\Collectable;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorResolverInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Core\Layer\LayerResolver;
use PHPUnit\Framework\TestCase;

final class LayerResolverTest extends TestCase
{
    public static function provideInvalidLayerConfigs(): iterable
    {
        yield 'empty config' => [
            [],
            InvalidLayerDefinitionException::class,
            'Layer configuration is empty. You need to define at least 1 layer.',
        ];

        yield 'layer with missing name' => [
            [
                [
                    'collectors' => [],
                ],
            ],
            InvalidLayerDefinitionException::class,
            'Could not resolve layer definition. The field "name" is required for all layers.',
        ];

        yield 'Duplicate layers' => [
            [
                [
                    'name' => 'test',
                    'collectors' => [],
                ],
                [
                    'name' => 'test',
                    'collectors' => [],
                ],
            ],
            InvalidLayerDefinitionException::class,
            'The layer "test" is empty. You must assign at least 1 collector to a layer.',
        ];

        yield 'Layer without other attributes' => [
            [
                [
                    'name' => 'test',
                ],
            ],
            InvalidLayerDefinitionException::class,
            'The layer "test" is empty. You must assign at least 1 collector to a layer.',
        ];

        yield 'Layer with empty collectors' => [
            [
                [
                    'name' => 'test',
                    'collectors' => [],
                ],
            ],
            InvalidLayerDefinitionException::class,
            'The layer "test" is empty. You must assign at least 1 collector to a layer.',
        ];
    }

    /**
     * @dataProvider provideInvalidLayerConfigs
     */
    public function testInvalidLayerConfigs(array $layers, string $exception, string $expectedMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($expectedMessage);

        $resolver = new LayerResolver(
            $this->createMock(CollectorResolverInterface::class),
            $layers
        );

        $resolver->has('Anything');
    }

    public function testHas(): void
    {
        $resolver = new LayerResolver(
            $this->buildCollectorResolverWithFakeCollector(),
            [
                [
                    'name' => 'test',
                    'collectors' => [
                        [
                            'type' => 'custom',
                        ],
                    ],
                ],
            ]
        );

        self::assertTrue($resolver->has('test'));
        self::assertFalse($resolver->has('other'));
    }

    public function testIsReferenceInLayer(): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));
        $resolver = new LayerResolver(
            $this->buildCollectorResolverWithFakeCollector(),
            [
                [
                    'name' => 'test',
                    'collectors' => [
                        [
                            'type' => 'custom',
                            'satisfy' => true,
                        ],
                    ],
                ],
            ]
        );

        self::assertTrue($resolver->isReferenceInLayer(
            'test',
            $reference,
        ));

        self::assertFalse($resolver->isReferenceInLayer(
            'other',
            $reference,
        ));
    }

    public function testGetLayersForReference(): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));
        $resolver = new LayerResolver(
            $this->buildCollectorResolverWithFakeCollector(),
            [
                [
                    'name' => 'test',
                    'collectors' => [
                        [
                            'type' => 'custom',
                            'satisfy' => true,
                        ],
                    ],
                ],
            ]
        );

        self::assertSame(
            ['test' => true],
            $resolver->getLayersForReference($reference)
        );
    }

    public function testGetLayersForReferenceWhenCollectorDoesNotSatisfy(): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('foo'));
        $resolver = new LayerResolver(
            $this->buildCollectorResolverWithFakeCollector(),
            [
                [
                    'name' => 'test',
                    'collectors' => [
                        [
                            'type' => 'custom',
                            'satisfy' => false,
                        ],
                    ],
                ],
            ]
        );

        self::assertSame(
            [],
            $resolver->getLayersForReference($reference)
        );
    }

    private function buildCollectorResolverWithFakeCollector(): CollectorResolverInterface
    {
        $collector = $this->createMock(CollectorInterface::class);
        $collector
            ->method('satisfy')
            ->with($this->callback(static fn (array $config): bool => 'custom' === $config['type']))
            ->willReturnCallback(static fn (array $config): bool => (bool) $config['satisfy'] ?? false)
        ;

        $resolver = $this->createMock(CollectorResolverInterface::class);
        $resolver
            ->method('resolve')
            ->with($this->callback(static fn (array $config): bool => 'custom' === $config['type']))
            ->willReturnCallback(static fn (array $config): Collectable => new Collectable($collector, $config))
        ;

        return $resolver;
    }
}
