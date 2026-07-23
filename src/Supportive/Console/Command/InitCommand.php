<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Supportive\File\Dumper as ConfigurationDumper;
use Deptrac\Deptrac\Supportive\File\Exception\FileAlreadyExistsException;
use Deptrac\Deptrac\Supportive\File\Exception\FileNotExistsException;
use Deptrac\Deptrac\Supportive\File\Exception\FileNotWritableException;
use Deptrac\Deptrac\Supportive\File\Exception\IOException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function getcwd;
use function sprintf;

#[AsCommand(
    name: 'init',
    description: 'Creates a depfile template',
)]
class InitCommand extends Command
{
    public function __construct(private readonly ConfigurationDumper $dumper)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var string $targetFile */
            $targetFile = $input->getParameterOption(['--config-file', '-c'], getcwd().DIRECTORY_SEPARATOR.'deptrac.php');
            $this->dumper->dump($targetFile);
            $output->writeln('Deptrac config <info>dumped.</info>');

            return self::SUCCESS;
        } catch (FileNotWritableException|FileAlreadyExistsException|IOException|FileNotExistsException $fileException) {
            $output->writeln(sprintf('<error>%s</error>', $fileException->getMessage()));

            return self::FAILURE;
        }
    }
}
