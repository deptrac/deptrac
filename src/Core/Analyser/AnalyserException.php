<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Analyser;

use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Contract\ExceptionInterface;
use Qossmic\Deptrac\Contract\Layer\CircularReferenceException;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Qossmic\Deptrac\Core\Ast\AstException;
use Qossmic\Deptrac\Core\Dependency\InvalidEmitterConfigurationException;
use Qossmic\Deptrac\Core\Dependency\UnrecognizedTokenException;
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
