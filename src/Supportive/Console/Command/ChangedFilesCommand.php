<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ChangedFilesCommand extends Command
{
    public static $defaultName = 'changed-files';
    public static $defaultDescription = 'Lists layers corresponding to the changed files';

    public function __construct(
        private readonly ChangedFilesRunner $runner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addOption('with-dependencies', null, InputOption::VALUE_NONE, 'show dependent layers');
        $this->addArgument('files', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'List of changed files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');

        $symfonyOutput = new SymfonyOutput($output, new Style(new SymfonyStyle($input, $output)));

        try {
            /** @var list<string> $files */
            $files = $input->getArgument('files');
            $this->runner->run($files, (bool) $input->getOption('with-dependencies'), $symfonyOutput);
        } catch (CommandRunException) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
