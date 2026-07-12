<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\Console\ConfigFileResolver;
use Deptrac\Deptrac\Supportive\DependencyInjection\Exception\CannotLoadConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;

final class ConfigFileResolverTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'deptrac_test_'.uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob("$this->tempDir/*"));
        rmdir($this->tempDir);
    }

    #[DataProvider('provideConfigFileArgv')]
    public function testResolveWithConfigFileArg(array $argv, string $expected): void
    {
        self::assertSame(
            $expected,
            (new ConfigFileResolver())->resolve(new ArgvInput($argv), '/cwd')
        );
    }

    public static function provideConfigFileArgv(): iterable
    {
        yield 'custom config before a command option' => [
            ['deptrac', 'analyse', '--config-file=custom.yaml', '--report-uncovered'],
            'custom.yaml',
        ];

        yield 'custom config after a command option' => [
            ['deptrac', 'analyse', '--report-uncovered', '--config-file=custom.yaml'],
            'custom.yaml',
        ];

        yield 'short custom config option' => [
            ['deptrac', 'analyse', '--report-uncovered', '-c', 'custom.yaml'],
            'custom.yaml',
        ];
    }

    #[DataProvider('provideAutoDetectConfig')]
    public function testResolveAutoDetect(string $touchFiles, string $expected): void
    {
        foreach (explode(',', $touchFiles) as $file) {
            touch($this->tempDir.DIRECTORY_SEPARATOR.trim($file));
        }

        self::assertSame(
            $this->tempDir.DIRECTORY_SEPARATOR.$expected,
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse']), $this->tempDir)
        );
    }

    public static function provideAutoDetectConfig(): iterable
    {
        yield 'deptrac.yaml only' => ['deptrac.yaml', 'deptrac.yaml'];
        yield 'deptrac.php only' => ['deptrac.php', 'deptrac.php'];
        yield 'both — prefer deptrac.php' => ['deptrac.php,deptrac.yaml', 'deptrac.php'];
    }

    public function testResolveThrowsWhenNoConfigFound(): void
    {
        $this->expectException(CannotLoadConfiguration::class);

        (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse']), $this->tempDir);
    }
}
