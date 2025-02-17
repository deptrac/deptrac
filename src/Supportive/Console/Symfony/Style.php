<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Symfony;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputStyleInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @internal
 */
class Style implements OutputStyleInterface
{
    public function __construct(private readonly SymfonyStyle $symfonyStyle)
    {
    }
    public function title(string $message) : void
    {
        $this->symfonyStyle->title($message);
    }
    public function section(string $message) : void
    {
        $this->symfonyStyle->section($message);
    }
    /**
     * {@inheritdoc}
     */
    public function success($message) : void
    {
        $this->symfonyStyle->success($message);
    }
    /**
     * {@inheritdoc}
     */
    public function error($message) : void
    {
        $this->symfonyStyle->error($message);
    }
    /**
     * {@inheritdoc}
     */
    public function warning($message) : void
    {
        $this->symfonyStyle->warning($message);
    }
    /**
     * {@inheritdoc}
     */
    public function note($message) : void
    {
        $this->symfonyStyle->note($message);
    }
    /**
     * {@inheritdoc}
     */
    public function caution($message) : void
    {
        $this->symfonyStyle->caution($message);
    }
    /**
     * {@inheritdoc}
     */
    public function definitionList(...$list) : void
    {
        $this->symfonyStyle->definitionList(...$list);
    }
    public function table(array $headers, array $rows) : void
    {
        $this->symfonyStyle->table($headers, $rows);
    }
    public function newLine(int $count = 1) : void
    {
        $this->symfonyStyle->newLine($count);
    }
    public function progressStart(int $max = 0) : void
    {
        $this->symfonyStyle->progressStart($max);
    }
    public function progressAdvance(int $step = 1) : void
    {
        $this->symfonyStyle->progressAdvance($step);
    }
    public function progressFinish() : void
    {
        $this->symfonyStyle->progressFinish();
    }
}
