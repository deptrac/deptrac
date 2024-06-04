<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\Console\Subscriber;

use Qossmic\Deptrac\Contract\Ast\AstFileAnalysedEvent;
use Qossmic\Deptrac\Contract\Ast\AstFileSyntaxErrorEvent;
use Qossmic\Deptrac\Contract\Ast\PostCreateAstMapEvent;
use Qossmic\Deptrac\Contract\Ast\PreCreateAstMapEvent;
use Qossmic\Deptrac\Contract\Dependency\PostEmitEvent;
use Qossmic\Deptrac\Contract\Dependency\PostFlattenEvent;
use Qossmic\Deptrac\Contract\Dependency\PreEmitEvent;
use Qossmic\Deptrac\Contract\Dependency\PreFlattenEvent;
use Qossmic\Deptrac\Contract\OutputFormatter\OutputInterface;
use Qossmic\Deptrac\Supportive\Time\Stopwatch;
use Qossmic\Deptrac\Supportive\Time\StopwatchException;
use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function sprintf;
class ConsoleSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly OutputInterface $output, private readonly Stopwatch $stopwatch)
    {
    }
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents() : array
    {
        return [PreCreateAstMapEvent::class => 'onPreCreateAstMapEvent', PostCreateAstMapEvent::class => 'onPostCreateAstMapEvent', AstFileAnalysedEvent::class => 'onAstFileAnalysedEvent', AstFileSyntaxErrorEvent::class => 'onAstFileSyntaxErrorEvent', PreEmitEvent::class => 'onPreDependencyEmit', PostEmitEvent::class => 'onPostDependencyEmit', PreFlattenEvent::class => 'onPreDependencyFlatten', PostFlattenEvent::class => 'onPostDependencyFlatten'];
    }
    public function onPreCreateAstMapEvent(PreCreateAstMapEvent $preCreateAstMapEvent) : void
    {
        if ($this->output->isVerbose()) {
            $this->stopwatchStart('ast');
            $this->output->writeLineFormatted(sprintf('Start to create an AstMap for <info>%u</info> Files.', $preCreateAstMapEvent->expectedFileCount));
        }
    }
    public function onPostCreateAstMapEvent(PostCreateAstMapEvent $postCreateAstMapEvent) : void
    {
        if ($this->output->isVerbose()) {
            $this->printMessageWithTime('ast', '<info>AstMap created in %01.2f sec.</info>', '<info>AstMap created.</info>');
        }
    }
    public function onAstFileAnalysedEvent(AstFileAnalysedEvent $analysedEvent) : void
    {
        if ($this->output->isVerbose()) {
            $this->output->writeLineFormatted(sprintf('Parsing File %s', $analysedEvent->file));
        }
    }
    public function onAstFileSyntaxErrorEvent(AstFileSyntaxErrorEvent $astFileSyntaxErrorEvent) : void
    {
        $this->output->writeLineFormatted(sprintf("\nSyntax Error on File %s\n<error>%s</error>\n", $astFileSyntaxErrorEvent->file, $astFileSyntaxErrorEvent->syntaxError));
    }
    public function onPreDependencyEmit(PreEmitEvent $event) : void
    {
        if ($this->output->isVerbose()) {
            $this->stopwatchStart('deps');
            $this->output->writeLineFormatted(sprintf('start emitting dependencies <info>"%s"</info>', $event->emitterName));
        }
    }
    public function onPostDependencyEmit(PostEmitEvent $event) : void
    {
        if ($this->output->isVerbose()) {
            $this->printMessageWithTime('deps', '<info>Dependencies emitted in %01.f sec.</info>', '<info>Dependencies emitted.</info>');
        }
    }
    public function onPreDependencyFlatten(PreFlattenEvent $event) : void
    {
        if ($this->output->isVerbose()) {
            $this->stopwatchStart('flatten');
            $this->output->writeLineFormatted('start flatten dependencies');
        }
    }
    public function onPostDependencyFlatten(PostFlattenEvent $event) : void
    {
        if ($this->output->isVerbose()) {
            $this->printMessageWithTime('flatten', '<info>Dependencies flattened in %01.f sec.</info>', '<info>Dependencies flattened.</info>');
        }
    }
    /**
     * @param non-empty-string $event
     */
    private function stopwatchStart(string $event) : void
    {
        try {
            $this->stopwatch->start($event);
        } catch (StopwatchException) {
        }
    }
    /**
     * @param non-empty-string $event
     * @param non-empty-string $messageWithTime
     * @param non-empty-string $messageWithoutTime
     */
    private function printMessageWithTime(string $event, string $messageWithTime, string $messageWithoutTime) : void
    {
        try {
            $period = $this->stopwatch->stop($event);
            $this->output->writeLineFormatted(sprintf($messageWithTime, $period->toSeconds()));
        } catch (StopwatchException) {
            $this->output->writeLineFormatted($messageWithoutTime);
        }
    }
}
