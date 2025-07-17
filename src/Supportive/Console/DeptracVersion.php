<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Console;

use Composer\InstalledVersions;

use function class_exists;

/**
 * @see https://github.com/php/pie/blob/main/src/Util/PieVersion.php
 *
 * The implementation is inspired by the PieVersion class from the Pie project.
 */
final class DeptracVersion
{
    private const SYMFONY_MAGIC_CONST_UNKNOWN = 'UNKNOWN';
    private const DEPTRAC_VERSION = '@git-version@';

    /**
     * If building as a PHAR with Box, Box will replace the value of DEPTRAC_VERSION at build time.If building as a PHAR with Box, Box will replace the value of DEPTRAC_VERSION at build time.
     */
    private static function hasGitVersion(): bool
    {
        return self::DEPTRAC_VERSION !== '@git-version@';
    }

    public static function get(): string
    {
        if (self::hasGitVersion()) {
            return self::DEPTRAC_VERSION;
        }

        /**
         * @see https://getcomposer.org/doc/07-runtime.md#installed-versions
         */
        if (!class_exists(InstalledVersions::class)) {
            return self::SYMFONY_MAGIC_CONST_UNKNOWN;
        }

        $installedVersion = InstalledVersions::getPrettyVersion('deptrac/deptrac');

        if (null === $installedVersion || '' === $installedVersion) {
            return self::SYMFONY_MAGIC_CONST_UNKNOWN;
        }

        return $installedVersion;
    }
}
