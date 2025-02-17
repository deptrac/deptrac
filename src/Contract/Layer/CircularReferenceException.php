<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Layer;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;
use function implode;
use function sprintf;
/**
 * Exception when there are circular dependencies between layers.
 *
 * Thrown when you use the `layer` collector and depend on a layer that
 * in turn depends back on you. To be able to resolve layers, the dependencies
 * between them have to be a DAG(Direct Acyclic Graph), otherwise
 * the resolution is not possible.
 */
final class CircularReferenceException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param list<string> $others
     */
    public static function circularLayerDependency(string $layer, array $others) : self
    {
        return new self(sprintf('Circular ruleset dependency for layer %s depending on: %s', $layer, implode('->', $others)));
    }
}
