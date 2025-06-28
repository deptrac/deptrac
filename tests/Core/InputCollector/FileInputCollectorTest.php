<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\InputCollector;

use Deptrac\Deptrac\Core\InputCollector\FileInputCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

use function array_values;
use function natcasesort;
use function sys_get_temp_dir;

final class FileInputCollectorTest extends TestCase
{
    public function testCollectsPhpFilesUsingAbsolutePath(): void
    {
        $collector = new FileInputCollector([__DIR__.'/Fixtures'], [], sys_get_temp_dir());

        $files = array_map(static function ($filePath) {
            return Path::normalize($filePath);
        }, $collector->collect());

        natcasesort($files);

        self::assertSame(
            [Path::normalize(__DIR__.'/Fixtures/example.php')],
            array_values($files)
        );
    }

    public function testCollectsPhpFilesUsingRelativePath(): void
    {
        $collector = new FileInputCollector(['Fixtures'], [], __DIR__);

        $files = array_map(static function ($filePath) {
            return Path::normalize($filePath);
        }, $collector->collect());

        natcasesort($files);

        self::assertSame(
            [Path::normalize(__DIR__.'/Fixtures/example.php')],
            array_values($files)
        );
    }
}
