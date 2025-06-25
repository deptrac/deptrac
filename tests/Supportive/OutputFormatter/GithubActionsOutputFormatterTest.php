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
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\GithubActionsOutputFormatter;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tests\Deptrac\Deptrac\Supportive\OutputFormatter\data\DummyViolationCreatingRule;

use const PHP_EOL;

final class GithubActionsOutputFormatterTest extends TestCase
{
    public function testGetName(): void
    {
        self::assertSame('github-actions', (new GithubActionsOutputFormatter())->getName());
    }

    /**
     * @dataProvider finishProvider
     */
    public function testFinish(array $rules, array $errors, array $warnings, string $expectedOutput): void
    {
        $bufferedOutput = new BufferedOutput();

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

        $formatter = new GithubActionsOutputFormatter();
        $formatter->finish(
            OutputResult::fromAnalysisResult($analysisResult),
            $this->createSymfonyOutput($bufferedOutput),
            new OutputFormatterInput(
                null,
                true,
                true,
                false
            )
        );

        self::assertSame($expectedOutput, $bufferedOutput->fetch());
    }

    public static function finishProvider(): iterable
    {
        yield 'No Rules, No Output' => [
            'rules' => [],
            'errors' => [],
            'warnings' => [],
            '',
        ];

        $originalA = ClassLikeToken::fromFQCN('\ACME\OriginalA');
        $originalB = ClassLikeToken::fromFQCN('\ACME\OriginalB');
        $originalAOccurrence = new FileOccurrence('/home/testuser/originalA.php', 12);

        yield 'Simple Violation' => [
            'violations' => [
                new Violation(
                    new Dependency($originalA, $originalB, new DependencyContext($originalAOccurrence, DependencyType::PARAMETER)),
                    'LayerA',
                    'LayerB',
                    new DummyViolationCreatingRule()
                ),
            ],
            'errors' => [],
            'warnings' => [],
            "::error file=/home/testuser/originalA.php,line=12::ACME\OriginalA must not depend on ACME\OriginalB (LayerA on LayerB)".PHP_EOL,
        ];

        yield 'Skipped Violation' => [
            'violations' => [
                new SkippedViolation(
                    new Dependency($originalA, $originalB, new DependencyContext($originalAOccurrence, DependencyType::PARAMETER)),
                    'LayerA',
                    'LayerB'
                ),
            ],
            'errors' => [],
            'warnings' => [],
            "::warning file=/home/testuser/originalA.php,line=12::[SKIPPED] ACME\OriginalA must not depend on ACME\OriginalB (LayerA on LayerB)".PHP_EOL,
        ];

        yield 'Uncovered Dependency' => [
            'violations' => [
                new Uncovered(
                    new Dependency($originalA, $originalB, new DependencyContext($originalAOccurrence, DependencyType::PARAMETER)),
                    'LayerA'
                ),
            ],
            'errors' => [],
            'warnings' => [],
            "::warning file=/home/testuser/originalA.php,line=12::ACME\OriginalA has uncovered dependency on ACME\OriginalB (LayerA)".PHP_EOL,
        ];

        yield 'Inherit dependency' => [
            'violations' => [
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
            'errors' => [],
            'warnings' => [],
            "::error file=originalA.php,line=12::ClassA must not depend on ClassB (LayerA on LayerB)%0AClassInheritD::6 ->%0AClassInheritC::5 ->%0AClassInheritB::4 ->%0AClassInheritA::3 ->%0AACME\OriginalB::12".PHP_EOL,
        ];

        yield 'an error occurred' => [
            'violations' => [],
            'errors' => [new Error('an error occurred')],
            'warnings' => [],
            '::error ::an error occurred'.PHP_EOL,
        ];

        yield 'an warning occurred' => [
            'violations' => [],
            'errors' => [],
            'warnings' => [
                Warning::tokenIsInMoreThanOneLayer(ClassLikeToken::fromFQCN('Foo\Bar')->toString(), ['Layer 1', 'Layer 2']),
            ],
            "::warning ::Foo\Bar is in more than one layer [\"Layer 1\", \"Layer 2\"]. It is recommended that one token should only be in one layer.".PHP_EOL,
        ];
    }

    public function testWithoutSkippedViolations(): void
    {
        $originalA = ClassLikeToken::fromFQCN('\ACME\OriginalA');
        $originalB = ClassLikeToken::fromFQCN('\ACME\OriginalB');
        $originalAOccurrence = new FileOccurrence('/home/testuser/originalA.php', 12);

        $analysisResult = new AnalysisResult();
        $analysisResult->addRule(
            new SkippedViolation(
                new Dependency($originalA, $originalB, new DependencyContext($originalAOccurrence, DependencyType::PARAMETER)),
                'LayerA',
                'LayerB'
            )
        );

        $bufferedOutput = new BufferedOutput();

        $formatter = new GithubActionsOutputFormatter();
        $formatter->finish(
            OutputResult::fromAnalysisResult($analysisResult),
            $this->createSymfonyOutput($bufferedOutput),
            new OutputFormatterInput(
                null,
                false,
                true,
                false,
            )
        );

        self::assertSame('', $bufferedOutput->fetch());
    }

    public function testUncoveredWithFailOnUncoveredAreReportedAsError(): void
    {
        $originalA = ClassLikeToken::fromFQCN('\ACME\OriginalA');
        $originalB = ClassLikeToken::fromFQCN('\ACME\OriginalB');
        $originalAOccurrence = new FileOccurrence('/home/testuser/originalA.php', 12);

        $analysisResult = new AnalysisResult();
        $analysisResult->addRule(
            new Uncovered(
                new Dependency($originalA, $originalB, new DependencyContext($originalAOccurrence, DependencyType::PARAMETER)),
                'LayerA'
            )
        );

        $bufferedOutput = new BufferedOutput();

        $formatter = new GithubActionsOutputFormatter();
        $formatter->finish(
            OutputResult::fromAnalysisResult($analysisResult),
            $this->createSymfonyOutput($bufferedOutput),
            new OutputFormatterInput(
                null,
                false,
                true,
                true,
            )
        );

        self::assertSame(
            "::error file=/home/testuser/originalA.php,line=12::ACME\OriginalA has uncovered dependency on ACME\OriginalB (LayerA)".PHP_EOL,
            $bufferedOutput->fetch()
        );
    }

    private function createSymfonyOutput(BufferedOutput $bufferedOutput): SymfonyOutput
    {
        return new SymfonyOutput(
            $bufferedOutput,
            new Style(new SymfonyStyle($this->createMock(InputInterface::class), $bufferedOutput))
        );
    }
}
