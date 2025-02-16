<?php

namespace Deptrac\Deptrac\Supportive\File;

use Deptrac\Deptrac\Supportive\File\Exception\CouldNotReadFileException;
use Deptrac\Deptrac\Supportive\File\Exception\FileCannotBeParsedAsYamlException;
use Deptrac\Deptrac\Supportive\File\Exception\ParsedYamlIsNotAnArrayException;
use DEPTRAC_INTERNAL\Symfony\Component\Yaml\Exception\ParseException;
use DEPTRAC_INTERNAL\Symfony\Component\Yaml\Yaml;
/**
 * @internal
 */
class YmlFileLoader
{
    /**
     * @return array{parameters: array<string, mixed>, services: array<string, mixed>, imports?: array<string>}
     *
     * @throws FileCannotBeParsedAsYamlException
     * @throws ParsedYamlIsNotAnArrayException
     * @throws CouldNotReadFileException
     */
    public function parseFile(string $file) : array
    {
        try {
            $data = Yaml::parse(\Deptrac\Deptrac\Supportive\File\FileReader::read($file));
        } catch (ParseException $exception) {
            throw FileCannotBeParsedAsYamlException::fromFilenameAndException($file, $exception);
        }
        if (!\is_array($data)) {
            throw ParsedYamlIsNotAnArrayException::fromFilename($file);
        }
        /** @var array{parameters: array<string, mixed>, services: array<string, mixed>, imports?: array<string>} $data */
        return $data;
    }
}
