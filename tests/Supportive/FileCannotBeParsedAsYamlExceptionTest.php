<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Utils;

use Deptrac\Deptrac\Supportive\File\Exception\FileCannotBeParsedAsYamlException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * @covers \Deptrac\Deptrac\Supportive\File\Exception\FileCannotBeParsedAsYamlException
 */
final class FileCannotBeParsedAsYamlExceptionTest extends TestCase
{
    public function testIsRuntimeException(): void
    {
        $exception = new FileCannotBeParsedAsYamlException();

        self::assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testFromFilenameAndExceptionReturnsException(): void
    {
        $filename = __FILE__;

        $exception = FileCannotBeParsedAsYamlException::fromFilenameAndException($filename, new ParseException('abc'));

        $message = sprintf(
            'File "%s" cannot be parsed as YAML: abc',
            $filename
        );

        self::assertSame($message, $exception->getMessage());
    }
}
