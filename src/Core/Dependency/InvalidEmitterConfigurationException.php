<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface;
use RuntimeException;
class InvalidEmitterConfigurationException extends RuntimeException implements ExceptionInterface
{
    public static function couldNotLocate(string $type) : self
    {
        return new self(\sprintf("Could not locate emitter type '%s' in the DI container.", $type));
    }
    public static function isNotEmitter(string $type, mixed $emitter) : self
    {
        $message = \sprintf('Type "%s" is not valid emitter (expected "%s", but is "%s").', $type, DependencyEmitterInterface::class, \get_debug_type($emitter));
        return new self($message);
    }
}
