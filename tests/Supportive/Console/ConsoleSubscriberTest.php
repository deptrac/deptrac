<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Contract\Ast\AstFileAnalysedEvent;
use Deptrac\Deptrac\Contract\Ast\AstFileSyntaxErrorEvent;
use Deptrac\Deptrac\Contract\Ast\PostCreateAstMapEvent;
use Deptrac\Deptrac\Contract\Ast\PreCreateAstMapEvent;
use Deptrac\Deptrac\Contract\Dependency\PostEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PostFlattenEvent;
use Deptrac\Deptrac\Contract\Dependency\PreEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PreFlattenEvent;
use Deptrac\Deptrac\Supportive\Console\Subscriber\ConsoleSubscriber;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use Deptrac\Deptrac\Supportive\Time\Stopwatch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use const PHP_EOL;

final class ConsoleSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [
                PreCreateAstMapEvent::class => 'onPreCreateAstMapEvent',
                PostCreateAstMapEvent::class => 'onPostCreateAstMapEvent',
                AstFileAnalysedEvent::class => 'onAstFileAnalysedEvent',
                AstFileSyntaxErrorEvent::class => 'onAstFileSyntaxErrorEvent',
                PreEmitEvent::class => 'onPreDependencyEmit',
                PostEmitEvent::class => 'onPostDependencyEmit',
                PreFlattenEvent::class => 'onPreDependencyFlatten',
                PostFlattenEvent::class => 'onPostDependencyFlatten',
            ],
            ConsoleSubscriber::getSubscribedEvents()
        );
    }

    public function testOnPreCreateAstMapEventWithVerboseVerbosity(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPreCreateAstMapEvent(new PreCreateAstMapEvent(9999999));

        self::assertSame('Start to create an AstMap for 9999999 Files.'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPreCreateAstMapEventWithStopwatchAlreadyStartedVerboseVerbosity(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $stopwatch = new Stopwatch();
        $stopwatch->start('ast');

        $subscriber = new ConsoleSubscriber($output, $stopwatch);
        $subscriber->onPreCreateAstMapEvent(new PreCreateAstMapEvent(9999999));

        self::assertSame('Start to create an AstMap for 9999999 Files.'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPostCreateAstMapEventWithVerboseVerbosityWithNoStopwatchStarted(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPostCreateAstMapEvent(new PostCreateAstMapEvent());

        self::assertSame('AstMap created.'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPostCreateAstMapEventWithVerboseVerbosity(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPreCreateAstMapEvent(new PreCreateAstMapEvent(9999999));
        $subscriber->onPostCreateAstMapEvent(new PostCreateAstMapEvent());

        self::assertMatchesRegularExpression(
            '/AstMap created in \d+\.\d+ sec\.'.PHP_EOL.'/',
            $symfonyOutput->fetch()
        );
    }

    public function testOnAstFileAnalysedEventWithVerboseVerbosity(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onAstFileAnalysedEvent(new AstFileAnalysedEvent('foo.php'));

        self::assertSame('Parsing File foo.php'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnAstFileSyntaxErrorEvent(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onAstFileSyntaxErrorEvent(
            new AstFileSyntaxErrorEvent('foo.php', 'Invalid')
        );

        self::assertSame("\nSyntax Error on File foo.php\nInvalid\n".PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPreDependencyEmit(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPreDependencyEmit(new PreEmitEvent('emitter-name'));

        self::assertSame('start emitting dependencies "emitter-name"'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPreDependencyEmitWithStopwatchAlreadyStarted(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $stopwatch = new Stopwatch();
        $stopwatch->start('deps');

        $subscriber = new ConsoleSubscriber($output, $stopwatch);
        $subscriber->onPreDependencyEmit(new PreEmitEvent('emitter-name'));

        self::assertSame('start emitting dependencies "emitter-name"'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPostDependencyEmitWithNoStopwatchStarted(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPostDependencyEmit(new PostEmitEvent());

        self::assertSame('Dependencies emitted.'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPostDependencyEmit(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPreDependencyEmit(new PreEmitEvent('emitter-name'));
        $subscriber->onPostDependencyEmit(new PostEmitEvent());

        self::assertMatchesRegularExpression(
            '/Dependencies emitted in \d+\.\d+ sec\.'.PHP_EOL.'/',
            $symfonyOutput->fetch()
        );
    }

    public function testOnPreDependencyFlatten(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPreDependencyFlatten(new PreFlattenEvent());

        self::assertSame('start flatten dependencies'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPreDependencyFlattenWithStopwatchStarted(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $stopwatch = new Stopwatch();
        $stopwatch->start('flatten');

        $subscriber = new ConsoleSubscriber($output, $stopwatch);
        $subscriber->onPreDependencyFlatten(new PreFlattenEvent());

        self::assertSame('start flatten dependencies'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPostDependencyFlattenWithNoStopwatchStarted(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPostDependencyFlatten(new PostFlattenEvent());

        self::assertSame('Dependencies flattened.'.PHP_EOL, $symfonyOutput->fetch());
    }

    public function testOnPostDependencyFlatten(): void
    {
        $symfonyOutput = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);
        $output = new SymfonyOutput($symfonyOutput, new Style(new SymfonyStyle(new ArrayInput([]), $symfonyOutput)));

        $subscriber = new ConsoleSubscriber($output, new Stopwatch());
        $subscriber->onPreDependencyFlatten(new PreFlattenEvent());
        $subscriber->onPostDependencyFlatten(new PostFlattenEvent());

        self::assertMatchesRegularExpression(
            '/Dependencies flattened in \d+\.\d+ sec\.'.PHP_EOL.'/',
            $symfonyOutput->fetch()
        );
    }
}
