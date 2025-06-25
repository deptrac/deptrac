<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\DependencyInjection\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

use function implode;
use function sprintf;

class InvalidServiceInLocatorException extends RuntimeException implements ExceptionInterface, ContainerExceptionInterface
{
    public static function invalidType(string $id, string $actualType, string ...$expectedTypes): self
    {
        $message = sprintf(
            'Trying to get unsupported service "%s" from locator (expected "%s", but is "%s").',
            $id,
            $actualType,
            implode('", "', $expectedTypes)
        );

        return new self($message);
    }
}
