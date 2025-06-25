<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\Contract\Analyser\AnalysisResult;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\Result\Allowed;
use Deptrac\Deptrac\Contract\Result\OutputResult;
use Deptrac\Deptrac\Contract\Result\Violation;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\FormatterConfiguration;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\MermaidJSOutputFormatter;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tests\Deptrac\Deptrac\Supportive\OutputFormatter\data\DummyViolationCreatingRule;

final class MermaidJSOutputFormatterTest extends TestCase
{
    public function testFinish(): void
    {
        $dependencyContext = new DependencyContext(
            new FileOccurrence('classA.php', 0),
            DependencyType::PARAMETER,
        );

        $dependency = new Dependency(
            ClassLikeToken::fromFQCN('ClassA'),
            ClassLikeToken::fromFQCN('ClassC'),
            $dependencyContext,
        );

        $analysisResult = new AnalysisResult();
        $analysisResult->addRule(new Allowed($dependency, 'LayerA', 'LayerB'));
        $analysisResult->addRule(new Allowed($dependency, 'LayerA', 'LayerB'));
        $analysisResult->addRule(new Allowed($dependency, 'LayerC', 'LayerD'));
        $analysisResult->addRule(new Allowed($dependency, 'LayerA', 'LayerC'));

        $analysisResult->addRule(new Violation($dependency, 'LayerA', 'LayerC', new DummyViolationCreatingRule()));
        $analysisResult->addRule(new Violation($dependency, 'LayerA', 'LayerC', new DummyViolationCreatingRule()));
        $analysisResult->addRule(new Violation($dependency, 'LayerB', 'LayerC', new DummyViolationCreatingRule()));

        $bufferedOutput = new BufferedOutput();

        $output = $this->createSymfonyOutput($bufferedOutput);
        $outputFormatterInput = new OutputFormatterInput(null, true, true, false);

        $mermaidJsConfig = [
            'mermaidjs' => [
                'direction' => 'TD',
                'groups' => [
                    'User' => [
                        'LayerA',
                        'LayerB',
                    ],
                    'Admin' => [
                        'LayerC',
                        'LayerD',
                    ],
                ],
                'default_node_options' => [],
            ],
        ];

        $mermaidJSOutputFormatter = new MermaidJSOutputFormatter(new FormatterConfiguration($mermaidJsConfig));
        $mermaidJSOutputFormatter->finish(OutputResult::fromAnalysisResult($analysisResult), $output, $outputFormatterInput);
        $this->assertSame(file_get_contents(__DIR__.'/data/mermaidjs-expected.txt'), $bufferedOutput->fetch());

        $mermaidJsConfig['mermaidjs']['default_node_options']['shape'] = 'circle';
        $mermaidJSOutputFormatter = new MermaidJSOutputFormatter(new FormatterConfiguration($mermaidJsConfig));
        $mermaidJSOutputFormatter->finish(OutputResult::fromAnalysisResult($analysisResult), $output, $outputFormatterInput);
        $this->assertSame(file_get_contents(__DIR__.'/data/mermaidjs-shape-circle.txt'), $bufferedOutput->fetch());

        $mermaidJsConfig['mermaidjs']['default_node_options']['shape'] = 'stadium';
        $mermaidJSOutputFormatter = new MermaidJSOutputFormatter(new FormatterConfiguration($mermaidJsConfig));
        $mermaidJSOutputFormatter->finish(OutputResult::fromAnalysisResult($analysisResult), $output, $outputFormatterInput);
        $this->assertSame(file_get_contents(__DIR__.'/data/mermaidjs-shape-stadium.txt'), $bufferedOutput->fetch());
    }

    private function createSymfonyOutput(BufferedOutput $bufferedOutput): SymfonyOutput
    {
        return new SymfonyOutput(
            $bufferedOutput,
            new Style(new SymfonyStyle($this->createMock(InputInterface::class), $bufferedOutput))
        );
    }
}
