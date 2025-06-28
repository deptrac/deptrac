<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Layer\Collectable;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorResolverInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\BoolCollector;
use PHPUnit\Framework\TestCase;

final class BoolCollectorTest extends TestCase
{
    private BoolCollector $collector;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->collector = new BoolCollector($resolver);
    }

    public static function providesatisfiableConfiguration(): iterable
    {
        yield 'must with satisfiable collector' => [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
            ],
            true,
        ];

        yield 'must with unsatisfiable collector' => [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
            ],
            false,
        ];

        yield 'must_not with satisfiable collector' => [
            [
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
            ],
            false,
        ];

        yield 'must_not with unsatisfiable collector' => [
            [
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
            ],
            true,
        ];

        yield 'must with multiple collectors, unsatisfiable' => [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
            ],
            false,
        ];

        yield 'must_not with multiple collectors, unsatisfiable' => [
            [
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
            ],
            false,
        ];

        yield 'mixed with must_not unsatisfiable' => [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
            ],
            false,
        ];

        yield 'mixed with must unsatisfiable' => [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
            ],
            false,
        ];

        yield 'mixed with all satisfiable' => [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
            ],
            true,
        ];
    }

    /**
     * @dataProvider provideSatisfiableConfiguration
     */
    public function testSatisfy(array $config, bool $expectedOutcome): void
    {
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo'));
        $actualOutcome = $this->collector->satisfy($config, $reference);

        self::assertSame($expectedOutcome, $actualOutcome);
    }

    public function testThrowsOnInvalidConfiguration(): void
    {
        $config = [
            [
                'must' => [
                    [
                        'type' => 'custom',
                        'satisfy' => true,
                    ],
                ],
                'must_not' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
                'invalid' => [
                    [
                        'type' => 'custom',
                        'satisfy' => false,
                    ],
                ],
            ],
            true,
        ];
        $reference = new ClassLikeReference(ClassLikeToken::fromFQCN('App\\Foo'));

        $this->expectException(InvalidCollectorDefinitionException::class);
        $this->expectExceptionMessage('BoolCollector: Missing a "must" or a "must_not" attribute.');

        $this->collector->satisfy($config, $reference);
    }
}
