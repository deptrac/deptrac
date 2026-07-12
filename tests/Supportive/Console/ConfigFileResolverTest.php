<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\Console;

use Deptrac\Deptrac\Supportive\Console\ConfigFileResolver;
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

    public function testResolveConfigFileBeforeCommandOption(): void
    {
        self::assertSame(
            'custom.yaml',
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse', '--config-file=custom.yaml', '--report-uncovered']), '/cwd')
        );
    }

    public function testResolveConfigFileAfterCommandOption(): void
    {
        self::assertSame(
            'custom.yaml',
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse', '--report-uncovered', '--config-file=custom.yaml']), '/cwd')
        );
    }

    public function testResolveShortOptionAfterCommandOption(): void
    {
        self::assertSame(
            'custom.yaml',
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse', '--report-uncovered', '-c', 'custom.yaml']), '/cwd')
        );
    }

    public function testResolveFallsBackToDeptracYaml(): void
    {
        touch($this->tempDir.DIRECTORY_SEPARATOR.'deptrac.yaml');

        self::assertSame(
            $this->tempDir.DIRECTORY_SEPARATOR.'deptrac.yaml',
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse']), $this->tempDir)
        );
    }

    public function testResolvePrefersDeptracPhp(): void
    {
        touch($this->tempDir.DIRECTORY_SEPARATOR.'deptrac.php');

        self::assertSame(
            $this->tempDir.DIRECTORY_SEPARATOR.'deptrac.php',
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse']), $this->tempDir)
        );
    }

    public function testResolvePrefersDeptracPhpWhenBothExist(): void
    {
        touch($this->tempDir.DIRECTORY_SEPARATOR.'deptrac.php');
        touch($this->tempDir.DIRECTORY_SEPARATOR.'deptrac.yaml');

        self::assertSame(
            $this->tempDir.DIRECTORY_SEPARATOR.'deptrac.php',
            (new ConfigFileResolver())->resolve(new ArgvInput(['deptrac', 'analyse']), $this->tempDir)
        );
    }
}
