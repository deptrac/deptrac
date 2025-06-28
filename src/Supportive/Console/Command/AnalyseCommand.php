<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\GithubActionsOutputFormatter;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\TableOutputFormatter;
use Deptrac\Deptrac\Supportive\Console\Env;
use Deptrac\Deptrac\Supportive\Console\Subscriber\ConsoleSubscriber;
use Deptrac\Deptrac\Supportive\Console\Subscriber\ProgressSubscriber;
use Deptrac\Deptrac\Supportive\Console\Symfony\Style;
use Deptrac\Deptrac\Supportive\Console\Symfony\SymfonyOutput;
use Deptrac\Deptrac\Supportive\OutputFormatter\FormatterProvider;
use Deptrac\Deptrac\Supportive\Time\Stopwatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AnalyseCommand extends Command
{
    final public const OPTION_REPORT_UNCOVERED = 'report-uncovered';
    final public const OPTION_FAIL_ON_UNCOVERED = 'fail-on-uncovered';
    final public const OPTION_REPORT_SKIPPED = 'report-skipped';

    public static $defaultName = 'analyse|analyze';
    public static $defaultDescription = 'Analyses your project using the provided depfile';

    public function __construct(
        private readonly AnalyseRunner $runner,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly FormatterProvider $formatterProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption(
            'formatter',
            'f',
            InputOption::VALUE_OPTIONAL,
            sprintf(
                'Format in which to print the result of the analysis. Possible: ["%s"]',
                implode('", "', $this->formatterProvider->getKnownFormatters())
            )
        );
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path for formatter (if applicable)');
        $this->addOption('no-progress', null, InputOption::VALUE_NONE, 'Do not show progress bar');
        $this->addOption(self::OPTION_FAIL_ON_UNCOVERED, null, InputOption::VALUE_NONE, 'Fails if any uncovered dependency is found');
        $this->addOption(self::OPTION_REPORT_UNCOVERED, null, InputOption::VALUE_NONE, 'Report uncovered dependencies');
        $this->addOption(self::OPTION_REPORT_SKIPPED, null, InputOption::VALUE_NONE, 'Report skipped violations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '-1');

        $symfonyOutput = new SymfonyOutput($output, new Style(new SymfonyStyle($input, $output)));
        /** @var ?string $formatter */
        $formatter = $input->getOption('formatter');
        $formatter ??= self::getDefaultFormatter();

        /** @var string|numeric|null $output */
        $output = $input->getOption('output');

        $options = new AnalyseOptions(
            (bool) $input->getOption('no-progress'),
            $formatter,
            null === $output ? null : (string) $output,
            (bool) $input->getOption(self::OPTION_REPORT_SKIPPED),
            (bool) $input->getOption(self::OPTION_REPORT_UNCOVERED),
            (bool) $input->getOption(self::OPTION_FAIL_ON_UNCOVERED)
        );

        $this->dispatcher->addSubscriber(new ConsoleSubscriber($symfonyOutput, new Stopwatch()));
        if (!$options->noProgress) {
            $this->dispatcher->addSubscriber(new ProgressSubscriber($symfonyOutput));
        }

        try {
            $this->runner->run($options, $symfonyOutput);
        } catch (CommandRunException) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    public static function getDefaultFormatter(): string
    {
        return false !== (new Env())->get('GITHUB_ACTIONS') ? GithubActionsOutputFormatter::getName() : TableOutputFormatter::getName();
    }
}
