<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\Console\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

final class ApplicationTest extends TestCase
{
    public function testDeterminesConfigFileRegardlessOfOptionOrder(): void
    {
        $cwd = '/cwd';

        // --config-file before a command option.
        self::assertSame(
            'custom.yaml',
            Application::determineConfigFile(
                new ArgvInput(['deptrac', 'analyse', '--config-file=custom.yaml', '--report-uncovered']),
                $cwd
            )
        );

        // --config-file after a command option (previously ignored, #1425).
        self::assertSame(
            'custom.yaml',
            Application::determineConfigFile(
                new ArgvInput(['deptrac', 'analyse', '--report-uncovered', '--config-file=custom.yaml']),
                $cwd
            )
        );

        // Short option form after a command option.
        self::assertSame(
            'custom.yaml',
            Application::determineConfigFile(
                new ArgvInput(['deptrac', 'analyse', '--report-uncovered', '-c', 'custom.yaml']),
                $cwd
            )
        );
    }

    public function testFallsBackToDefaultConfigFileInWorkingDirectory(): void
    {
        self::assertSame(
            '/cwd'.DIRECTORY_SEPARATOR.'deptrac.yaml',
            Application::determineConfigFile(new ArgvInput(['deptrac', 'analyse']), '/cwd')
        );
    }
}
