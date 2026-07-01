<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\Console\ConfigFileResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

final class ConfigFileResolverTest extends TestCase
{
    #[DataProvider('provideConfigFile')]
    public function testResolve(array $argv, string $currentWorkingDirectory, string $expected): void
    {
        self::assertSame(
            $expected,
            (new ConfigFileResolver())->resolve(new ArgvInput($argv), $currentWorkingDirectory)
        );
    }

    /**
     * @return iterable<string, array{list<string>, string, string}>
     */
    public static function provideConfigFile(): iterable
    {
        yield 'config-file before a command option' => [
            ['deptrac', 'analyse', '--config-file=custom.yaml', '--report-uncovered'],
            '/cwd',
            'custom.yaml',
        ];

        // Regression for #1425: the config file was ignored when an unknown
        // command option preceded it.
        yield 'config-file after a command option' => [
            ['deptrac', 'analyse', '--report-uncovered', '--config-file=custom.yaml'],
            '/cwd',
            'custom.yaml',
        ];

        yield 'short option after a command option' => [
            ['deptrac', 'analyse', '--report-uncovered', '-c', 'custom.yaml'],
            '/cwd',
            'custom.yaml',
        ];

        yield 'falls back to deptrac.yaml in the working directory' => [
            ['deptrac', 'analyse'],
            '/cwd',
            '/cwd'.DIRECTORY_SEPARATOR.'deptrac.yaml',
        ];
    }
}
