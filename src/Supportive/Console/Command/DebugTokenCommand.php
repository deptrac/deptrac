<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\Console\Command;

use Qossmic\Deptrac\Core\Analyser\TokenType;
use Qossmic\Deptrac\Supportive\Console\Symfony\Style;
use Qossmic\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Command\Command;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputArgument;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Output\OutputInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Style\SymfonyStyle;
class DebugTokenCommand extends Command
{
    public static $defaultName = 'debug:token|debug:class-like';
    public static $defaultDescription = 'Checks which layers the provided token belongs to';
    public function __construct(private readonly \Qossmic\Deptrac\Supportive\Console\Command\DebugTokenRunner $runner)
    {
        parent::__construct();
    }
    protected function configure() : void
    {
        parent::configure();
        $this->addArgument('token', InputArgument::REQUIRED, 'Full qualified token name to debug');
        $this->addArgument('type', InputArgument::OPTIONAL, 'Token type (class-like, function, file)', 'class-like');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $outputStyle = new Style(new SymfonyStyle($input, $output));
        $symfonyOutput = new SymfonyOutput($output, $outputStyle);
        /** @var string $tokenName */
        $tokenName = $input->getArgument('token');
        /** @var string $tokenType */
        $tokenType = $input->getArgument('type');
        try {
            $this->runner->run($tokenName, TokenType::from($tokenType), $symfonyOutput);
        } catch (\Qossmic\Deptrac\Supportive\Console\Command\CommandRunException $exception) {
            $outputStyle->error($exception->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
