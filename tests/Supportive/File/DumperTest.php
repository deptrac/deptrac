<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\File;

use Deptrac\Deptrac\Supportive\File\Dumper;
use Deptrac\Deptrac\Supportive\File\Exception\FileAlreadyExistsException;
use Deptrac\Deptrac\Supportive\File\Exception\FileNotWritableException;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function file_exists;
use function file_get_contents;
use function is_writable;
use function rtrim;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;

final class DumperTest extends TestCase
{
    private string $sourceFile;
    private Dumper $dumper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceFile = __DIR__.'/Fixtures/deptrac.yaml';
        $this->dumper = new Dumper($this->sourceFile);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->dumper);
    }

    public function testFailsWhenFileAlreadyExists(): void
    {
        $targetFile = $this->sourceFile;

        $this->expectException(FileAlreadyExistsException::class);

        $this->dumper->dump($targetFile);
    }

    public function testFailsWhenFileIsNotWritable(): void
    {
        $tempDir = sprintf('%s/%s', rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR), uniqid());
        if (!mkdir($tempDir) || !chmod($tempDir, 0444)) {
            $this->markTestSkipped(sprintf('Skipping test. Could not create readonly temporary directory "%s". Please check permissions', $tempDir));
        }
        $tempFile = new SplFileInfo($tempDir.DIRECTORY_SEPARATOR.'deptrac.yaml');

        $this->expectException(FileNotWritableException::class);

        $this->dumper->dump($tempFile->getPathname());
    }

    public function testCopiesTemplateToNewFile(): void
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'deptrac');
        if (false === $tempFilename) {
            $this->markTestSkipped('Skipping test. Could not create temporary file. Please check permissions');
        }
        $tempFile = new SplFileInfo($tempFilename);
        unlink($tempFile->getPathname());
        $tempDir = $tempFile->getPath();
        if (!is_writable($tempDir)) {
            $this->markTestSkipped(sprintf('Skipping test. Can not write to temporary directory "%s". Please check your permissions.', $tempDir));
        }
        if ($tempFile->isFile()) {
            $this->fail(sprintf('Temporary file "%s" already exists.', $tempFile->getPathname()));
        }

        $this->dumper->dump($tempFile->getPathname());

        self::assertTrue(file_exists($tempFile->getPathname()));
        self::assertSame(file_get_contents($this->sourceFile), file_get_contents($tempFile->getPathname()));

        @unlink($tempFile->getPathname());
        @rmdir($tempDir);
    }
}
