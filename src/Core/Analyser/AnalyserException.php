<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\ExceptionInterface;
use Deptrac\Deptrac\Contract\Layer\CircularReferenceException;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;
use RuntimeException;
final class AnalyserException extends RuntimeException implements ExceptionInterface
{
    public static function invalidEmitterConfiguration(InvalidEmitterConfigurationException $e) : self
    {
        return new self('Invalid emitter configuration.', 0, $e);
    }
    public static function unrecognizedToken(UnrecognizedTokenException $e) : self
    {
        return new self('Unrecognized token.', 0, $e);
    }
    public static function invalidLayerDefinition(InvalidLayerDefinitionException $e) : self
    {
        return new self('Invalid layer definition.', 0, $e);
    }
    public static function invalidCollectorDefinition(InvalidCollectorDefinitionException $e) : self
    {
        return new self('Invalid collector definition.', 0, $e);
    }
    public static function failedAstParsing(AstException $e) : self
    {
        return new self('Failed Ast parsing.', 0, $e);
    }
    public static function couldNotParseFile(CouldNotParseFileException $e) : self
    {
        return new self('Could not parse file.', 0, $e);
    }
    public static function circularReference(CircularReferenceException $e) : self
    {
        return new self('Circular layer dependency.', 0, $e);
    }
}
