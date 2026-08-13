<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Layer\Collectable;
use Deptrac\Deptrac\Contract\Layer\CollectorResolverInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\BaselineMapperInterface;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Core\Analyser\DependencyLayersAnalyser;
use Deptrac\Deptrac\Core\Ast\AstLoader;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Core\Dependency\DependencyResolver;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\InputCollector\InputCollectorInterface;
use Deptrac\Deptrac\Core\Layer\LayerProvider;
use Deptrac\Deptrac\Core\Layer\LayerResolver;
use Deptrac\Deptrac\DefaultBehavior\Analyser\AllowDependencyHandler;
use Deptrac\Deptrac\DefaultBehavior\Analyser\DependsOnDisallowedLayer;
use Deptrac\Deptrac\DefaultBehavior\Analyser\MatchingLayersHandler;
use Deptrac\Deptrac\DefaultBehavior\Analyser\UncoveredDependentHandler;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\ClassLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\FunctionLikeExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\MethodCallExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Extractors\NewExtractor;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\DefaultBehavior\Dependency\ClassDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Dependency\MethodDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Layer\AttributeCollector;
use Deptrac\Deptrac\DefaultBehavior\Layer\ClassNameRegexCollector;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * End-to-end test for method-level layer granularity: methods collected into a
 * layer of their own ("scope: method") are checked against the ruleset
 * independently of their class.
 */
final class DependencyLayersAnalyserTest extends TestCase
{
    private const FIXTURE_NAMESPACE = 'Tests\Deptrac\Deptrac\Core\Analyser\Fixtures';

    public function testMethodGranularityDetectsInwardDependencyViolation(): void
    {
        $result = OutputResult::fromAnalysisResult($this->analyseFixture());

        $violations = $result->violations();
        self::assertCount(1, $violations);

        $violation = $violations[0];
        self::assertInstanceOf(Violation::class, $violation);
        self::assertSame('Application', $violation->getDependerLayer());
        self::assertSame('Infrastructure', $violation->getDependentLayer());
        self::assertSame(
            self::FIXTURE_NAMESPACE.'\RegisterBookFeature::handleRegister()',
            $violation->getDependency()->getDepender()->toString()
        );
        self::assertSame(
            self::FIXTURE_NAMESPACE.'\RegisterBookFeature::registerBook()',
            $violation->getDependency()->getDependent()->toString()
        );
        self::assertSame(24, $violation->getDependency()->getContext()->fileOccurrence->line);
    }

    public function testMethodGranularityAllowsOutwardDependencies(): void
    {
        $result = OutputResult::fromAnalysisResult($this->analyseFixture());

        // Infrastructure (the class, including the unlayered registerBook method)
        // may depend on Application (the handleRegister method and BookCommand)
        $allowedDescriptions = array_map(
            static fn ($allowed): string => sprintf(
                '%s -> %s (%s -> %s)',
                $allowed->getDependency()->getDepender()->toString(),
                $allowed->getDependency()->getDependent()->toString(),
                $allowed->getDependerLayer(),
                $allowed->getDependentLayer()
            ),
            $result->allowed()
        );

        self::assertEqualsCanonicalizing(
            [
                self::FIXTURE_NAMESPACE.'\RegisterBookFeature -> '.self::FIXTURE_NAMESPACE.'\RegisterBookFeature::handleRegister() (Infrastructure -> Application)',
                self::FIXTURE_NAMESPACE.'\RegisterBookFeature -> '.self::FIXTURE_NAMESPACE.'\BookCommand (Infrastructure -> Application)',
            ],
            $allowedDescriptions
        );

        self::assertSame([], $result->warnings);
        self::assertSame([], $result->errors);
    }

    private function analyseFixture(): \Deptrac\Deptrac\Contract\Analyser\AnalysisResult
    {
        $fixtureFile = __DIR__.'/Fixtures/RegisterBookFeature.php';

        $typeResolver = new TypeResolver();
        $phpStanContainer = new PhpStanContainerDecorator(__DIR__, __DIR__, [$fixtureFile]);
        $parser = new NikicPhpParser(
            (new ParserFactory())->createForNewestSupportedVersion(),
            new AstFileReferenceInMemoryCache(),
            [
                new ClassLikeExtractor($phpStanContainer, $typeResolver),
                new FunctionLikeExtractor($typeResolver),
                new NewExtractor($typeResolver),
                new MethodCallExtractor(),
            ]
        );

        $inputCollector = new class($fixtureFile) implements InputCollectorInterface {
            public function __construct(private readonly string $file) {}

            public function collect(): array
            {
                return [$this->file];
            }
        };

        $eventDispatcher = new EventDispatcher();
        $astMapExtractor = new AstMapExtractor($inputCollector, new AstLoader($parser, $eventDispatcher));

        $collectorResolver = new class implements CollectorResolverInterface {
            public function resolve(array $config): Collectable
            {
                $collector = match ($config['type']) {
                    'attribute' => new AttributeCollector(),
                    'classNameRegex' => new ClassNameRegexCollector(),
                };

                return new Collectable($collector, $config);
            }
        };

        $layerResolver = new LayerResolver($collectorResolver, [
            [
                'name' => 'Infrastructure',
                'collectors' => [
                    ['type' => 'attribute', 'value' => 'AsController'],
                ],
            ],
            [
                'name' => 'Application',
                'collectors' => [
                    ['type' => 'attribute', 'value' => 'AsEventListener', 'scope' => 'method'],
                    ['type' => 'classNameRegex', 'value' => '/BookCommand$/'],
                ],
            ],
        ]);

        $analyserConfig = ['types' => ['class', 'method']];
        $emitters = [
            'class' => new ClassDependencyEmitter($analyserConfig),
            'method' => new MethodDependencyEmitter($layerResolver),
        ];
        $emitterLocator = new class($emitters) implements ContainerInterface {
            public function __construct(private readonly array $emitters) {}

            public function get(string $id): mixed
            {
                return $this->emitters[$id];
            }

            public function has(string $id): bool
            {
                return isset($this->emitters[$id]);
            }
        };

        $baselineMapper = new class implements BaselineMapperInterface {
            public function fromPHPListToString(array $groupedViolations): string
            {
                return '';
            }

            public function loadViolations(): array
            {
                return [];
            }
        };
        $eventHelper = new EventHelper(new LayerProvider(['Infrastructure' => ['Application']]), $baselineMapper);

        $eventDispatcher->addSubscriber(new UncoveredDependentHandler(true));
        $eventDispatcher->addSubscriber(new MatchingLayersHandler());
        $eventDispatcher->addSubscriber(new DependsOnDisallowedLayer($eventHelper));
        $eventDispatcher->addSubscriber(new AllowDependencyHandler());

        $analyser = new DependencyLayersAnalyser(
            $astMapExtractor,
            new DependencyResolver($analyserConfig, $emitterLocator, $eventDispatcher),
            new TokenResolver(),
            $layerResolver,
            $eventDispatcher
        );

        return $analyser->analyse();
    }
}
