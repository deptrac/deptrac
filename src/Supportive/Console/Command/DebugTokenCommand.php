<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Core\Analyser\TokenType;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugTokenCommand extends Command
{
    public static $defaultName = 'debug:token|debug:class-like';
    public static $defaultDescription = 'Checks which layers the provided token belongs to';

    public function __construct(private readonly DebugTokenRunner $runner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('token', InputArgument::REQUIRED, 'Full qualified token name to debug');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Token type (class-like, function, file)', 'class-like');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputStyle = new Style(new SymfonyStyle($input, $output));
        $symfonyOutput = new SymfonyOutput($output, $outputStyle);

        /** @var string $tokenName */
        $tokenName = $input->getArgument('token');
        /** @var string $tokenType */
        $tokenType = $input->getArgument('type');

        try {
            $this->runner->run($tokenName, TokenType::from($tokenType), $symfonyOutput);
        } catch (CommandRunException $exception) {
            $outputStyle->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
