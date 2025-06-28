<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugDependenciesCommand extends Command
{
    public static $defaultName = 'debug:dependencies';
    public static $defaultDescription = 'List layer dependencies';

    public function __construct(private readonly DebugDependenciesRunner $runner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        /** @throws void */
        $this->addArgument('layer', InputArgument::REQUIRED, 'Layer to debug');
        /** @throws void */
        $this->addArgument(
            'targetLayer',
            InputArgument::OPTIONAL,
            'Target layer to filter dependencies to only one layer'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputStyle = new Style(new SymfonyStyle($input, $output));
        $symfonyOutput = new SymfonyOutput($output, $outputStyle);

        try {
            /** @var ?string $target */
            $target = $input->getArgument('targetLayer');

            /** @var string $layer */
            $layer = $input->getArgument('layer');

            $this->runner->run($symfonyOutput, $layer, $target);
        } catch (CommandRunException $exception) {
            $outputStyle->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
