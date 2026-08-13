<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Config;

/**
 * Restricts which token references a collector is applied to.
 *
 * TYPE_CLASS (the default) applies the collector to the references deptrac
 * assigns by default: class-likes, functions, files and superglobals.
 * TYPE_METHOD applies the collector to class method references only, assigning
 * individual methods to a layer of their own.
 */
enum CollectorScope: string
{
    case TYPE_CLASS = 'class';
    case TYPE_METHOD = 'method';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $scope): string => $scope->value,
            self::cases()
        );
    }
}
