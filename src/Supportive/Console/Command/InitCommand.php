<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Supportive\File\Dumper as ConfigurationDumper;
use Deptrac\Deptrac\Supportive\File\Exception\FileAlreadyExistsException;
use Deptrac\Deptrac\Supportive\File\Exception\FileNotExistsException;
use Deptrac\Deptrac\Supportive\File\Exception\FileNotWritableException;
use Deptrac\Deptrac\Supportive\File\Exception\IOException;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Command\Command;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Output\OutputInterface;
use function sprintf;
class InitCommand extends Command
{
    public static $defaultName = 'init';
    public static $defaultDescription = 'Creates a depfile template';
    public function __construct(private readonly ConfigurationDumper $dumper)
    {
        parent::__construct();
    }
    protected function configure() : void
    {
        parent::configure();
        $this->setName('init');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        try {
            /** @var string $targetFile */
            $targetFile = $input->getOption('config-file');
            $this->dumper->dump($targetFile);
            $output->writeln('Depfile <info>dumped.</info>');
            return self::SUCCESS;
        } catch (FileNotWritableException|FileAlreadyExistsException|IOException|FileNotExistsException $fileException) {
            $output->writeln(sprintf('<error>%s</error>', $fileException->getMessage()));
            return self::FAILURE;
        }
    }
}
