<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer\Helpers;

use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;

abstract class RegexCollector implements CollectorInterface
{
    /**
     * @throws InvalidCollectorDefinitionException
     */
    abstract protected function getPattern(string $config): string;

    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    protected function getValidatedPattern(array $config): string
    {
        if (!isset($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(static::getClassName().': Missing configuration.');
        }
        if (!is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(static::getClassName().': Configuration is not a string.');
        }

        $pattern = $this->getPattern($config['value']);
        if (false !== @preg_match($pattern, '')) {
            return $pattern;
        }
        throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(static::getClassName().': Invalid regex pattern '.$pattern);
    }

    protected static function getClassName(): string
    {
        $lastPart = strrchr(static::class, '\\');

        return false === $lastPart ? static::class : substr($lastPart, 1);
    }
}
