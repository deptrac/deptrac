<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\DependencyInjection\Exception\CannotLoadConfiguration;
use Symfony\Component\Console\Input\InputInterface;

use function is_file;
use function realpath;

final class ConfigFileResolver
{
    private const CANDIDATES = [
        'deptrac.php',
        'deptrac.yaml',
    ];

    /**
     * Resolve the configuration file from the raw input tokens.
     *
     * This reads directly from the tokens rather than from a bound option
     * because the input binding in {@see Application::doRun()} is only partial
     * (command options are not known yet) and aborts on the first unknown
     * option. Reading the bound option would therefore miss `--config-file`
     * whenever another option precedes it on the command line. This mirrors how
     * `--cache-file` and `--no-cache` are read.
     */
    public function resolve(InputInterface $input, string $currentWorkingDirectory): string
    {
        /** @var string|numeric|false $configFile */
        $configFile = $input->getParameterOption(['--config-file', '-c'], false);

        if (false !== $configFile) {
            return (string) $configFile;
        }

        foreach (self::CANDIDATES as $candidate) {
            $path = $currentWorkingDirectory.DIRECTORY_SEPARATOR.$candidate;
            if (is_file($path)) {
                return realpath($path);
            }
        }

        CannotLoadConfiguration::cannotFind();
    }
}
