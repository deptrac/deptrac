<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\OutputFormatter;

use DateTimeImmutable;
use Deptrac\Deptrac\Contract\Analyser\AnalysisResult;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\Result\Allowed;
use Deptrac\Deptrac\Contract\Result\Error;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\RuleInterface;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\Core\Dependency\InheritDependency;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\JUnitOutputFormatter;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tests\Deptrac\Deptrac\Supportive\OutputFormatter\data\DummyViolationCreatingRule;

final class JUnitOutputFormatterTest extends TestCase
{
    private static $actual_junit_report_file = 'actual-junit-report.xml';

    public function tearDown(): void
    {
        if (file_exists(__DIR__.'/data/'.self::$actual_junit_report_file)) {
            unlink(__DIR__.'/data/'.self::$actual_junit_report_file);
        }
    }

    public function testGetName(): void
    {
        self::assertSame('junit', (new JUnitOutputFormatter())->getName());
    }

    /**
     * @return iterable<array{list<RuleInterface|Error>, string}>
     */
    public static function basicDataProvider(): iterable
    {
        $originalA = ClassLikeToken::fromFQCN('OriginalA');
        $originalB = ClassLikeToken::fromFQCN('OriginalB');
        $classInheritA = ClassLikeToken::fromFQCN('ClassInheritA');
        $classInheritB = ClassLikeToken::fromFQCN('ClassInheritB');
        $classInheritC = ClassLikeToken::fromFQCN('ClassInheritC');
        $classInheritD = ClassLikeToken::fromFQCN('ClassInheritD');

        yield [
            [
                new Violation(
                    new InheritDependency(
                        ClassLikeToken::fromFQCN('ClassA'),
                        ClassLikeToken::fromFQCN('ClassB'),
                        new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('foo.php', 12), DependencyType::PARAMETER)),
                        (new AstInherit(
                            $classInheritA, new FileOccurrence('foo.php', 3),
                            AstInheritType::EXTENDS
                        ))->replacePath([
                            new AstInherit(
                                $classInheritB, new FileOccurrence('foo.php', 4),
                                AstInheritType::EXTENDS
                            ),
                            new AstInherit(
                                $classInheritC, new FileOccurrence('foo.php', 5),
                                AstInheritType::EXTENDS
                            ),
                            new AstInherit(
                                $classInheritD, new FileOccurrence('foo.php', 6),
                                AstInheritType::EXTENDS
                            ),
                        ])
                    ),
                    'LayerA',
                    'LayerB',
                    new DummyViolationCreatingRule()
                ),
            ],
            'expected-junit-report_1.xml',
        ];

        yield [
            [
                new Violation(
                    new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('foo.php', 12), DependencyType::PARAMETER)),
                    'LayerA',
                    'LayerB',
                    new DummyViolationCreatingRule()
                ),
            ],
            'expected-junit-report_2.xml',
        ];

        yield [
            [
                new Allowed(
                    new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('foo.php', 12), DependencyType::PARAMETER)),
                    'LayerA',
                    'LayerB',
                ),
            ],
            'expected-junit-report_3.xml',
        ];

        yield [
            [
                new SkippedViolation(
                    new InheritDependency(
                        ClassLikeToken::fromFQCN('ClassA'),
                        ClassLikeToken::fromFQCN('ClassB'),
                        new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('foo.php', 12), DependencyType::PARAMETER)),
                        (new AstInherit(
                            $classInheritA, new FileOccurrence('foo.php', 3),
                            AstInheritType::EXTENDS
                        ))->replacePath([
                            new AstInherit(
                                $classInheritB, new FileOccurrence('foo.php', 4),
                                AstInheritType::EXTENDS
                            ),
                            new AstInherit(
                                $classInheritC, new FileOccurrence('foo.php', 5),
                                AstInheritType::EXTENDS
                            ),
                            new AstInherit(
                                $classInheritD, new FileOccurrence('foo.php', 6),
                                AstInheritType::EXTENDS
                            ),
                        ])
                    ),
                    'LayerA',
                    'LayerB'
                ),
                new Violation(
                    new InheritDependency(
                        ClassLikeToken::fromFQCN('ClassC'),
                        ClassLikeToken::fromFQCN('ClassD'),
                        new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('foo.php', 12), DependencyType::PARAMETER)),
                        (new AstInherit(
                            $classInheritA, new FileOccurrence('foo.php', 3),
                            AstInheritType::EXTENDS
                        ))->replacePath([
                            new AstInherit(
                                $classInheritB, new FileOccurrence('foo.php', 4),
                                AstInheritType::EXTENDS
                            ),
                            new AstInherit(
                                $classInheritC, new FileOccurrence('foo.php', 5),
                                AstInheritType::EXTENDS
                            ),
                            new AstInherit(
                                $classInheritD, new FileOccurrence('foo.php', 6),
                                AstInheritType::EXTENDS
                            ),
                        ])
                    ),
                    'LayerA',
                    'LayerB',
                    new DummyViolationCreatingRule()
                ),
            ],
            'expected-junit-report-with-skipped-violations.xml',
        ];

        yield [
            [
                new Uncovered(
                    new Dependency($originalA, $originalB, new DependencyContext(new FileOccurrence('foo.php', 12), DependencyType::PARAMETER)),
                    'test'
                ),
            ],
            'expected-junit-report-with-uncovered.xml',
        ];

        yield [
            [
                new Error('Skipped violation "Class1" for "Class2" was not matched.'),
            ],
            'expected-junit-report-with-unmatched-violations.xml',
        ];
    }

    /**
     * @dataProvider basicDataProvider
     *
     * @param list<RuleInterface|Error> $rules
     */
    public function testBasic(array $rules, string $expectedOutputFile): void
    {
        $analysisResult = new AnalysisResult(new DateTimeImmutable('2025-03-28T22:17:43'));
        foreach ($rules as $rule) {
            if ($rule instanceof RuleInterface) {
                $analysisResult->addRule($rule);
            } else {
                $analysisResult->addError($rule);
            }
        }

        $formatter = new JUnitOutputFormatter();
        $formatter->finish(
            OutputResult::fromAnalysisResult($analysisResult),
            $this->createSymfonyOutput(new BufferedOutput()),
            new OutputFormatterInput(__DIR__.'/data/'.self::$actual_junit_report_file,
                true, true, true)
        );

        $reader = new DOMDocument();
        $reader->load(__DIR__.'/data/'.self::$actual_junit_report_file);
        libxml_use_internal_errors(true);

        self::assertTrue($reader->schemaValidate(__DIR__.'/data/junit-schema-ant.xsd'),
            implode(array_map(static fn ($e) => $e->line.': '.$e->message, libxml_get_errors())));
        self::assertTrue($reader->schemaValidate(__DIR__.'/data/junit-schema-jenkins.xsd'),
            implode(array_map(static fn ($e) => $e->message, libxml_get_errors())));
        self::assertTrue($reader->schemaValidate(__DIR__.'/data/junit-schema-llg.xsd'),
            implode(array_map(static fn ($e) => $e->message, libxml_get_errors())));
        self::assertTrue($reader->schemaValidate(__DIR__.'/data/junit-schema-maven.xsd'),
            implode(array_map(static fn ($e) => $e->message, libxml_get_errors())));

        self::assertXmlFileEqualsXmlFile(
            __DIR__.'/data/'.self::$actual_junit_report_file,
            __DIR__.'/data/'.$expectedOutputFile
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
