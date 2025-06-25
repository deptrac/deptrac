<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Contract\Analyser;

use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\OutputFormatter\BaselineMapperInterface;
use Deptrac\Deptrac\Core\Layer\LayerProvider;
use PHPUnit\Framework\TestCase;

final class EventHelperTest extends TestCase
{
    public function testIsViolationSkipped(): void
    {
        $configuration = [
            'ClassWithOneDep' => [
                'DependencyClass',
            ],
            'ClassWithEmptyDeps' => [],
            'ClassWithMultipleDeps' => [
                'DependencyClass1',
                'DependencyClass2',
                'DependencyClass2',
            ],
        ];

        $baselineMapper = new class($configuration) implements BaselineMapperInterface {
            public function __construct(private readonly array $violations) {}

            public function fromPHPListToString(array $groupedViolations): string
            {
                return '';
            }

            public function loadViolations(): array
            {
                return $this->violations;
            }
        };

        $helper = new EventHelper(new LayerProvider([]), $baselineMapper);

        self::assertTrue(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithOneDep')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass')->toString()
            )
        );
        // also skips multiple occurrences
        self::assertTrue(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithOneDep')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass')->toString()
            )
        );
        self::assertFalse(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithEmptyDeps')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass')->toString()
            )
        );
        self::assertTrue(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithMultipleDeps')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass1')->toString()
            )
        );
        self::assertTrue(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithMultipleDeps')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass2')->toString()
            )
        );
        self::assertFalse(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('DependencyClass')->toString(),
                ClassLikeToken::fromFQCN('ClassWithOneDep')->toString()
            )
        );
    }

    public function testUnmatchedSkippedViolations(): void
    {
        $configuration = [
            'ClassWithOneDep' => [
                'DependencyClass',
            ],
            'ClassWithEmptyDeps' => [],
            'ClassWithMultipleDeps' => [
                'DependencyClass1',
                'DependencyClass2',
                'DependencyClass2',
            ],
        ];

        $baselineMapper = new class($configuration) implements BaselineMapperInterface {
            public function __construct(private readonly array $violations) {}

            public function fromPHPListToString(array $groupedViolations): string
            {
                return '';
            }

            public function loadViolations(): array
            {
                return $this->violations;
            }
        };

        $helper = new EventHelper(new LayerProvider([]), $baselineMapper);

        self::assertTrue(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithOneDep')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass')->toString()
            )
        );
        // also skips multiple occurrences
        self::assertTrue(
            $helper->shouldViolationBeSkipped(
                ClassLikeToken::fromFQCN('ClassWithOneDep')->toString(),
                ClassLikeToken::fromFQCN('DependencyClass')->toString()
            )
        );
        self::assertSame(
            [
                'ClassWithMultipleDeps' => [
                    'DependencyClass1',
                    'DependencyClass2',
                    'DependencyClass2',
                ],
            ],
            $helper->unmatchedSkippedViolations()
        );
    }
}
