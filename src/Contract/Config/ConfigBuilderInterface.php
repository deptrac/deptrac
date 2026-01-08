<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

/**
 * A ConfigBuilder provides helper methods to build configuration arrays.
 *
 * This interface replaces Symfony's deprecated ConfigBuilderInterface
 * for Deptrac's internal configuration API.
 */
interface ConfigBuilderInterface
{
    /**
     * Gets all configuration represented as an array.
     *
     * @return array<mixed>
     */
    public function toArray(): array;

    /**
     * Gets the alias for the extension which config we are building.
     */
    public function getExtensionAlias(): string;
}
