<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\File;

use Deptrac\Deptrac\Supportive\File\Exception\CouldNotReadFileException;
final class FileReader
{
    /**
     * @throws CouldNotReadFileException
     */
    public static function read(string $fileName) : string
    {
        $contents = @\file_get_contents($fileName);
        if (\false === $contents) {
            throw CouldNotReadFileException::fromFilename($fileName);
        }
        return $contents;
    }
}
