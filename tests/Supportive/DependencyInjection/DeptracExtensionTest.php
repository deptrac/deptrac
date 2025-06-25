<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Config\EmitterType;
use Deptrac\Deptrac\Supportive\DependencyInjection\DeptracExtension;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function krsort;

final class DeptracExtensionTest extends TestCase
{
    private ContainerBuilder $container;
    private DeptracExtension $extension;

    private const FORMATTER_DEFAULTS = [
        'graphviz' => [
            'hidden_layers' => [],
            'groups' => [],
            'point_to_groups' => false,
        ],
        'mermaidjs' => [
            'direction' => 'TD',
            'groups' => [],
            'default_node_options' => [],
        ],
        'codeclimate' => [
            'severity' => [
                'failure' => 'major',
                'skipped' => 'minor',
                'uncovered' => 'info',
            ],
        ],
    ];

    private const ANALYSER_DEFAULTS = [
        'internal_tag' => null,
        'types' => [
            // Unfortunately, we can't use the enum type here, see
            // https://wiki.php.net/rfc/fetch_property_in_const_expressions
            'class',    // ClassLikeType::CLASS
            'function', // ClassLikeType::FUNCTION
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->extension = new DeptracExtension();
    }

    public function testDefaults(): void
    {
        $configs = [];

        $this->extension->load($configs, $this->container);

        self::assertSame(['src/'], $this->container->getParameter('paths'));
        self::assertSame([], $this->container->getParameter('exclude_files'));
        self::assertSame([], $this->container->getParameter('layers'));
        self::assertSame([], $this->container->getParameter('ruleset'));
        self::assertSame([], $this->container->getParameter('skip_violations'));
        self::assertSame(self::FORMATTER_DEFAULTS, $this->container->getParameter('formatters'));
        self::assertSame(self::ANALYSER_DEFAULTS, $this->container->getParameter('analyser'));
        self::assertSame(true, $this->container->getParameter('ignore_uncovered_internal_classes'));
        self::assertSame('.deptrac.cache', $this->container->getParameter('cache_file'));
    }

    public function testDefaultsWithEmptyRoot(): void
    {
        $configs = [
            'deptrac' => [],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['src/'], $this->container->getParameter('paths'));
        self::assertSame([], $this->container->getParameter('exclude_files'));
        self::assertSame([], $this->container->getParameter('layers'));
        self::assertSame([], $this->container->getParameter('ruleset'));
        self::assertSame([], $this->container->getParameter('skip_violations'));
        self::assertSame(self::FORMATTER_DEFAULTS, $this->container->getParameter('formatters'));
        self::assertSame(self::ANALYSER_DEFAULTS, $this->container->getParameter('analyser'));
        self::assertSame(true, $this->container->getParameter('ignore_uncovered_internal_classes'));
        self::assertSame('.deptrac.cache', $this->container->getParameter('cache_file'));
    }

    public function testPathsWithMultipleElements(): void
    {
        $configs = [
            'deptrac' => [
                'paths' => ['tests/', 'src/', 'lib/'],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['tests/', 'src/', 'lib/'], $this->container->getParameter('paths'));
    }

    public function testPathsWithSingleElement(): void
    {
        $configs = [
            'deptrac' => [
                'paths' => ['lib/'],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['lib/'], $this->container->getParameter('paths'));
    }

    public function testPathsWithScalar(): void
    {
        $configs = [
            'deptrac' => [
                'path' => 'tests/',
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['tests/'], $this->container->getParameter('paths'));
    }

    public function testExcludePatternWithMultipleElements(): void
    {
        $configs = [
            'deptrac' => [
                'exclude_files' => ['.*', '/\d+/'],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['.*', '/\d+/'], $this->container->getParameter('exclude_files'));
    }

    public function testExcludePatternsWithSingleElement(): void
    {
        $configs = [
            'deptrac' => [
                'exclude_files' => ['#.*Test\.php#'],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['#.*Test\.php#'], $this->container->getParameter('exclude_files'));
    }

    public function testExcludePatternsWithScalar(): void
    {
        $configs = [
            'deptrac' => [
                'exclude_file' => '/^[a-z]+$/',
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(['/^[a-z]+$/'], $this->container->getParameter('exclude_files'));
    }

    public function testNullLayers(): void
    {
        $configs = [
            'deptrac' => [
                'layers' => null,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame([], $this->container->getParameter('layers'));
    }

    public function testEmptyLayers(): void
    {
        $configs = [
            'deptrac' => [
                'layers' => [],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame([], $this->container->getParameter('layers'));
    }

    public function testLayers(): void
    {
        $configs = [
            'deptrac' => [
                'layers' => [
                    [
                        'name' => 'test',
                        'collectors' => [
                            [
                                'type' => 'directory',
                                'value' => 'Repository',
                                'attributes' => [],
                            ],
                        ],
                        'attributes' => [],
                    ],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            [
                'test' => [
                    'name' => 'test',
                    'collectors' => [
                        [
                            'type' => 'directory',
                            'value' => 'Repository',
                            'attributes' => [],
                            'private' => false,
                        ],
                    ],
                    'attributes' => [],
                ],
            ],
            $this->container->getParameter('layers')
        );
    }

    public function testNullRuleset(): void
    {
        $configs = [
            'deptrac' => [
                'ruleset' => null,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            [],
            $this->container->getParameter('ruleset')
        );
    }

    public function testEmptyRuleset(): void
    {
        $configs = [
            'deptrac' => [
                'ruleset' => [],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            [],
            $this->container->getParameter('ruleset')
        );
    }

    public function testRuleset(): void
    {
        $configs = [
            'deptrac' => [
                'ruleset' => [
                    'Foo' => ['Bar', 'Baz'],
                    'Bar' => null,
                    'Baz' => [],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            [
                'Foo' => ['Bar', 'Baz'],
                'Bar' => [],
                'Baz' => [],
            ],
            $this->container->getParameter('ruleset')
        );
    }

    public function testSkipViolations(): void
    {
        $configs = [
            'deptrac' => [
                'skip_violations' => [
                    'examples\Layer2\SomeOtherClass' => [
                        'examples\Layer1\SomeClass',
                    ],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            [
                'examples\Layer2\SomeOtherClass' => [
                    'examples\Layer1\SomeClass',
                ],
            ],
            $this->container->getParameter('skip_violations')
        );
    }

    public function testNullAnalyser(): void
    {
        $configs = [
            'deptrac' => [
                'analyser' => null,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            self::ANALYSER_DEFAULTS,
            $this->container->getParameter('analyser')
        );
    }

    public function testEmptyAnalyser(): void
    {
        $configs = [
            'deptrac' => [
                'analyser' => [],
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            self::ANALYSER_DEFAULTS,
            $this->container->getParameter('analyser')
        );
    }

    public function testInvalidAnalyserTypes(): void
    {
        $configs = [
            'deptrac' => [
                'analyser' => [
                    'types' => ['invalid'],
                ] + self::ANALYSER_DEFAULTS,
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type "invalid"');

        $this->extension->load($configs, $this->container);
    }

    public function testNullAnalyserTypes(): void
    {
        $configs = [
            'deptrac' => [
                'analyser' => [
                    'types' => null,
                ] + self::ANALYSER_DEFAULTS,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            ['types' => []] + self::ANALYSER_DEFAULTS,
            $this->container->getParameter('analyser')
        );
    }

    public function testEmptyAnalyserTypes(): void
    {
        $configs = [
            'deptrac' => [
                'analyser' => [
                    'types' => [],
                ] + self::ANALYSER_DEFAULTS,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            ['types' => []] + self::ANALYSER_DEFAULTS,
            $this->container->getParameter('analyser')
        );
    }

    public function testAnalyserWithDuplicateTypes(): void
    {
        $configs = [
            'deptrac' => [
                'analyser' => [
                    'types' => [EmitterType::CLASS_TOKEN->value, EmitterType::CLASS_TOKEN->value],
                ] + self::ANALYSER_DEFAULTS,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(
            ['types' => [EmitterType::CLASS_TOKEN->value, EmitterType::CLASS_TOKEN->value]]
                + self::ANALYSER_DEFAULTS,
            $this->container->getParameter('analyser')
        );
    }

    public function testIgnoreUncoveredInternalClasses(): void
    {
        $configs = [
            'deptrac' => [
                'ignore_uncovered_internal_classes' => false,
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame(false, $this->container->getParameter('ignore_uncovered_internal_classes'));
    }

    public function testCacheFile(): void
    {
        $configs = [
            'deptrac' => [
                'cache_file' => 'test.path',
            ],
        ];

        $this->extension->load($configs, $this->container);

        self::assertSame('test.path', $this->container->getParameter('cache_file'));
    }

    public function testGraphvizFormatterWithEmptyNodes(): void
    {
        $configs = [
            'deptrac' => [
                'formatters' => [
                    'graphviz' => [],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        $expectedFormatterConfig = self::FORMATTER_DEFAULTS;
        $actualFormatterConfig = $this->container->getParameter('formatters');

        krsort($expectedFormatterConfig);
        krsort($actualFormatterConfig);

        self::assertSame($expectedFormatterConfig, $actualFormatterConfig);
    }

    public function testGraphvizFormatterWithOldPointToGroupsConfig(): void
    {
        $configs = [
            'deptrac' => [
                'formatters' => [
                    'graphviz' => [
                        'pointToGroups' => true,
                    ],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        $expectedFormatterConfig = self::FORMATTER_DEFAULTS;
        $expectedFormatterConfig['graphviz'] = [
            'point_to_groups' => true,
            'hidden_layers' => [],
            'groups' => [],
        ];

        self::assertSame($expectedFormatterConfig, $this->container->getParameter('formatters'));
    }

    public function testGraphvizFormattersWithHiddenLayers(): void
    {
        $configs = [
            'deptrac' => [
                'formatters' => [
                    'graphviz' => [
                        'hidden_layers' => ['Utils'],
                        'groups' => [
                            'App' => ['Controller', 'View'],
                            'Domain' => ['User', 'Checkout', 'Product'],
                        ],
                        'point_to_groups' => true,
                    ],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        $expectedFormatterConfig = self::FORMATTER_DEFAULTS;
        $expectedFormatterConfig['graphviz'] = [
            'hidden_layers' => ['Utils'],
            'groups' => [
                'App' => ['Controller', 'View'],
                'Domain' => ['User', 'Checkout', 'Product'],
            ],
            'point_to_groups' => true,
        ];

        self::assertSame($expectedFormatterConfig, $this->container->getParameter('formatters'));
    }

    public function testCodeclimateFormatterWithEmptyNodes(): void
    {
        $configs = [
            'deptrac' => [
                'formatters' => [
                    'codeclimate' => [],
                ],
            ],
        ];

        $this->extension->load($configs, $this->container);

        $expectedFormatterConfig = self::FORMATTER_DEFAULTS;
        $actualFormatterConfig = $this->container->getParameter('formatters');

        krsort($expectedFormatterConfig);
        krsort($actualFormatterConfig);

        self::assertSame($expectedFormatterConfig, $actualFormatterConfig);
    }

    public function testCodeclimateFormatterRequiresValidSeverity(): void
    {
        $configs = [
            'deptrac' => [
                'formatters' => [
                    'codeclimate' => [
                        'severity' => [
                            'failure' => 'super duper important',
                        ],
                    ],
                ],
            ],
        ];

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The value "super duper important" is not allowed for path "deptrac.formatters.codeclimate.severity.failure".');

        $this->extension->load($configs, $this->container);
    }
}
