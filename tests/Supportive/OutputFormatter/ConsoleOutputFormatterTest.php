<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\Contract\Analyser\AnalysisResult;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\Result\Error;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Contract\Result\Warning;
use Deptrac\Deptrac\Core\Dependency\InheritDependency;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\ConsoleOutputFormatter;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tests\Deptrac\Deptrac\Supportive\OutputFormatter\data\DummyViolationCreatingRule;

final class ConsoleOutputFormatterTest extends TestCase
{
    public function testGetName(): void
    {
        self::assertSame('console', (new ConsoleOutputFormatter())->getName());
    }

    public static function basicDataProvider(): iterable
    {
        $originalA = ClassLikeToken::fromFQCN('OriginalA');
        $originalB = ClassLikeToken::fromFQCN('OriginalB');

        yield [
            [
                new Violation(
                    new InheritDependency(
                        ClassLikeToken::fromFQCN('ClassA'),
                        ClassLikeToken::fromFQCN('ClassB'),
                        new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('originalA.php', 12), DependencyType::PARAMETER)),
                        (new AstInherit(
                            ClassLikeToken::fromFQCN('ClassInheritA'), new FileOccurrence('originalA.php', 3),
                            AstInheritType::EXTENDS
                        ))
                            ->replacePath([
                                new AstInherit(
                                    ClassLikeToken::fromFQCN('ClassInheritB'),
                                    new FileOccurrence('originalA.php', 4),
                                    AstInheritType::EXTENDS
                                ),
                                new AstInherit(
                                    ClassLikeToken::fromFQCN('ClassInheritC'),
                                    new FileOccurrence('originalA.php', 5),
                                    AstInheritType::EXTENDS
                                ),
                                new AstInherit(
                                    ClassLikeToken::fromFQCN('ClassInheritD'),
                                    new FileOccurrence('originalA.php', 6),
                                    AstInheritType::EXTENDS
                                ),
                            ])
                    ),
                    'LayerA',
                    'LayerB',
                    new DummyViolationCreatingRule()
                ),
            ],
            [],
            'warnings' => [],
            '
                ClassA must not depend on ClassB (LayerA on LayerB)
                originalA.php:12
                ClassInheritD:6 ->
                ClassInheritC:5 ->
                ClassInheritB:4 ->
                ClassInheritA:3 ->
                OriginalB:12

                Report:
                Violations: 1
                Skipped violations: 0
                Uncovered: 0
                Allowed: 0
                Warnings:0
                Errors:0
            ',
        ];

        yield [
            [
                new Violation(
                    new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('originalA.php', 12), DependencyType::PARAMETER)),
                    'LayerA',
                    'LayerB',
                    new DummyViolationCreatingRule()
                ),
            ],
            [],
            'warnings' => [],
            '
                OriginalA must not depend on OriginalB (LayerA on LayerB)
                originalA.php:12

                Report:
                Violations: 1
                Skipped violations: 0
                Uncovered: 0
                Allowed: 0
                Warnings:0
                Errors:0
            ',
        ];

        yield [
            [],
            [],
            'warnings' => [],
            '

                Report:
                Violations: 0
                Skipped violations: 0
                Uncovered: 0
                Allowed: 0
                Warnings:0
                Errors:0
            ',
        ];

        yield [
            [
                new SkippedViolation(
                    new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('originalA.php', 12), DependencyType::PARAMETER)),
                    'LayerA',
                    'LayerB'
                ),
            ],
            [],
            'warnings' => [],
            '[SKIPPED] OriginalA must not depend on OriginalB (LayerA on LayerB)
            originalA.php:12

            Report:
            Violations: 0
            Skipped violations: 1
            Uncovered: 0
            Allowed: 0
            Warnings:0
            Errors:0
            ',
        ];

        yield [
            [
                new Uncovered(
                    new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('originalA.php', 12), DependencyType::PARAMETER)),
                    'LayerA'
                ),
                new Uncovered(
                    new InheritDependency(
                        ClassLikeToken::fromFQCN('ClassA'),
                        ClassLikeToken::fromFQCN('ClassB'),
                        new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('originalA.php', 12), DependencyType::PARAMETER)),
                        (new AstInherit(
                            ClassLikeToken::fromFQCN('ClassInheritA'), new FileOccurrence('originalA.php', 3),
                            AstInheritType::EXTENDS
                        ))
                            ->replacePath([
                                new AstInherit(
                                    ClassLikeToken::fromFQCN('ClassInheritB'),
                                    new FileOccurrence('originalA.php', 4),
                                    AstInheritType::EXTENDS
                                ),
                                new AstInherit(
                                    ClassLikeToken::fromFQCN('ClassInheritC'),
                                    new FileOccurrence('originalA.php', 5),
                                    AstInheritType::EXTENDS
                                ),
                                new AstInherit(
                                    ClassLikeToken::fromFQCN('ClassInheritD'),
                                    new FileOccurrence('originalA.php', 6),
                                    AstInheritType::EXTENDS
                                ),
                            ])
                    ),
                    'LayerA'
                ),
            ],
            [],
            'warnings' => [],
            '
                Uncovered dependencies:
                OriginalA has uncovered dependency on OriginalB (LayerA)
                originalA.php:12
                ClassA has uncovered dependency on ClassB (LayerA)
                originalA.php:12
                ClassInheritD:6 -> ClassInheritC:5 -> ClassInheritB:4 -> ClassInheritA:3 -> OriginalB:12
                Report:
                Violations: 0
                Skipped violations: 0
                Uncovered: 2
                Allowed: 0
                Warnings:0
                Errors:0
            ',
        ];

        yield 'an error occurred' => [
            [],
            [new Error('an error occurred')],
            'warnings' => [],
            '[ERROR]anerroroccurredReport:Violations:0Skippedviolations:0Uncovered:0Allowed:0Warnings:0Errors:1',
        ];

        yield 'an warning occurred' => [
            [],
            [],
            'warnings' => [Warning::tokenIsInMoreThanOneLayer(ClassLikeToken::fromFQCN('Foo\Bar')->toString(), ['Layer 1', 'Layer 2'])],
            '[WARNING]Foo\Barisinmorethanonelayer["Layer1","Layer2"].Itisrecommendedthatonetokenshouldonlybeinonelayer.Report:Violations:0Skippedviolations:0Uncovered:0Allowed:0Warnings:1Errors:0',
        ];
    }

    /**
     * @dataProvider basicDataProvider
     */
    public function testBasic(array $rules, array $errors, array $warnings, string $expectedOutput): void
    {
        $bufferedOutput = new BufferedOutput();
        $output = new SymfonyOutput(
            $bufferedOutput,
            new Style(new SymfonyStyle($this->createMock(InputInterface::class), $bufferedOutput))
        );

        $analysisResult = new AnalysisResult();
        foreach ($rules as $rule) {
            $analysisResult->addRule($rule);
        }
        foreach ($errors as $error) {
            $analysisResult->addError($error);
        }
        foreach ($warnings as $warning) {
            $analysisResult->addWarning($warning);
        }

        $formatter = new ConsoleOutputFormatter();
        $formatter->finish(
            OutputResult::fromAnalysisResult($analysisResult),
            $output,
            new OutputFormatterInput(
                null,
                true,
                true,
                false,
            )
        );

        $o = $bufferedOutput->fetch();
        self::assertSame(
            $this->normalize($expectedOutput),
            $this->normalize($o)
        );
    }

    public function testWithoutSkippedViolations(): void
    {
        $originalA = ClassLikeToken::fromFQCN('OriginalA');
        $originalB = ClassLikeToken::fromFQCN('OriginalB');

        $analysisResult = new AnalysisResult();
        $analysisResult->addRule(new SkippedViolation(
            new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('originalA.php', 12), DependencyType::PARAMETER)),
            'LayerA',
            'LayerB'
        ));

        $bufferedOutput = new BufferedOutput();
        $output = new SymfonyOutput(
            $bufferedOutput,
            new Style(new SymfonyStyle($this->createMock(InputInterface::class), $bufferedOutput))
        );

        $formatter = new ConsoleOutputFormatter();
        $formatter->finish(
            OutputResult::fromAnalysisResult($analysisResult),
            $output,
            new OutputFormatterInput(
                null,
                false,
                true,
                false,
            )
        );

        $o = $bufferedOutput->fetch();

        $expectedOutput = '

            Report:
            Violations: 0
            Skipped violations: 1
            Uncovered: 0
            Allowed: 0
            Warnings:0
            Errors:0
            ';

        self::assertSame(
            $this->normalize($expectedOutput),
            $this->normalize($o)
        );
    }

    private function normalize($str)
    {
        return str_replace(["\r", "\t", "\n", ' '], '', $str);
    }
}
