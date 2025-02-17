<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\DependencyInjection\Exception\CannotLoadConfiguration;
use Deptrac\Deptrac\Supportive\DependencyInjection\ServiceContainerBuilder;
use RuntimeException;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Application as BaseApplication;
use DEPTRAC_INTERNAL\Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Exception\ExceptionInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Exception\InvalidArgumentException;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputDefinition;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Input\InputOption;
use DEPTRAC_INTERNAL\Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use function getcwd;
use function in_array;
use const DIRECTORY_SEPARATOR;
final class Application extends BaseApplication
{
    public const VERSION = '2.0.5';
    public function __construct()
    {
        parent::__construct('deptrac', self::VERSION);
    }
    /**
     * @throws InvalidArgumentException
     */
    protected function getDefaultInputDefinition() : InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOptions([new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display help for the given command. When no command is given display help for the <info>analyse</info> command'), new InputOption('--no-cache', null, InputOption::VALUE_NONE, 'Disable caching mechanisms (wins over --cache-file)'), new InputOption('--clear-cache', null, InputOption::VALUE_NONE, 'Clears cache file before run'), new InputOption('--cache-file', null, InputOption::VALUE_REQUIRED, 'Location where cache file will be stored', null), new InputOption('--config-file', '-c', InputOption::VALUE_REQUIRED, 'Location of Depfile containing the configuration', getcwd() . DIRECTORY_SEPARATOR . 'deptrac.yaml')]);
        return $definition;
    }
    /**
     * @throws Throwable
     */
    public function doRun(InputInterface $input, OutputInterface $output) : int
    {
        if (\false === ($currentWorkingDirectory = getcwd())) {
            throw \Deptrac\Deptrac\Supportive\Console\CannotGetCurrentWorkingDirectoryException::cannotGetCWD();
        }
        try {
            $input->bind($this->getDefinition());
        } catch (ExceptionInterface) {
            // Errors must be ignored, full binding/validation happens later when the command is known.
        }
        if (null === $input->getArgument('command') && \true === $input->getOption('version')) {
            return parent::doRun($input, $output);
        }
        /** @var string|numeric|null $configFile */
        $configFile = $input->getOption('config-file');
        $config = $input->hasOption('config-file') ? (string) $configFile : $currentWorkingDirectory . DIRECTORY_SEPARATOR . 'deptrac.yaml';
        /** @var ?string $cache */
        $cache = $input->getParameterOption('--cache-file', null);
        $factory = new ServiceContainerBuilder($currentWorkingDirectory);
        if (!in_array($input->getArgument('command'), ['init', 'list', 'help', 'completion'], \true)) {
            $factory = $factory->withConfig($config);
        }
        $noCache = $input->hasParameterOption('--no-cache', \true);
        try {
            $container = $factory->build($noCache ? \false : $cache, $input->hasParameterOption('--clear-cache', \true));
            $commandLoader = $container->get('console.command_loader');
            if (!$commandLoader instanceof CommandLoaderInterface) {
                throw new RuntimeException('CommandLoader not initialized. Commands can not be registered.');
            }
            $this->setCommandLoader($commandLoader);
            $this->setDefaultCommand('analyse');
        } catch (CannotLoadConfiguration $e) {
            if (\false === $input->hasParameterOption(['--help', '-h'], \true)) {
                throw $e;
            }
            $this->setDefaultCommand('help');
        }
        return parent::doRun($input, $output);
    }
}
