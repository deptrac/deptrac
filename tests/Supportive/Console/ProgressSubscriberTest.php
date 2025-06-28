<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Contract\Ast\AstFileAnalysedEvent;
use Deptrac\Deptrac\Contract\Ast\PostCreateAstMapEvent;
use Deptrac\Deptrac\Contract\Ast\PreCreateAstMapEvent;
use Deptrac\Deptrac\Supportive\Console\Subscriber\ProgressSubscriber;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use const PHP_EOL;

final class ProgressSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [
                PreCreateAstMapEvent::class => 'onPreCreateAstMapEvent',
                PostCreateAstMapEvent::class => ['onPostCreateAstMapEvent', 1],
                AstFileAnalysedEvent::class => 'onAstFileAnalysedEvent',
            ],
            ProgressSubscriber::getSubscribedEvents()
        );
    }

    public function testProgress(): void
    {
        $bufferedOutput = new BufferedOutput();
        $subscriber = new ProgressSubscriber($this->createSymfonyOutput($bufferedOutput));

        $subscriber->onPreCreateAstMapEvent(new PreCreateAstMapEvent(1));
        $subscriber->onAstFileAnalysedEvent(new AstFileAnalysedEvent('foo.php'));
        $subscriber->onPostCreateAstMapEvent(new PostCreateAstMapEvent());

        $expectedOutput = " 0/1 [{$this->getEmptyBarOutput()}]   0%".PHP_EOL.
            " 1/1 [{$this->getBarOutput()}] 100%".PHP_EOL.PHP_EOL;

        self::assertSame($expectedOutput, $bufferedOutput->fetch());
    }

    public function testOnPostCreateAstMapEvent(): void
    {
        $formatter = new BufferedOutput();
        $subscriber = new ProgressSubscriber($this->createSymfonyOutput($formatter));

        $subscriber->onPreCreateAstMapEvent(new PreCreateAstMapEvent(1));
        $subscriber->onPostCreateAstMapEvent(new PostCreateAstMapEvent());

        $expectedOutput = " 0/1 [{$this->getEmptyBarOutput()}]   0%".PHP_EOL.
            " 1/1 [{$this->getBarOutput()}] 100%".PHP_EOL.PHP_EOL;

        self::assertSame($expectedOutput, $formatter->fetch());
    }

    private function createSymfonyOutput(BufferedOutput $bufferedOutput): SymfonyOutput
    {
        return new SymfonyOutput(
            $bufferedOutput,
            new Style(new SymfonyStyle($this->createMock(InputInterface::class), $bufferedOutput))
        );
    }

    private function getEmptyBarOutput(): string
    {
        $progressChar = $this->getProgressBar()->getProgressCharacter();

        return $progressChar.str_repeat($this->getProgressBar()->getEmptyBarCharacter(), '' === $progressChar ? 28 : 27);
    }

    private function getBarOutput(): string
    {
        return str_repeat($this->getProgressBar()->getBarCharacter(), 28);
    }

    private function getProgressBar(): ProgressBar
    {
        $style = new SymfonyStyle($this->createMock(InputInterface::class), $this->createMock(OutputInterface::class));

        return $style->createProgressBar(28);
    }
}
