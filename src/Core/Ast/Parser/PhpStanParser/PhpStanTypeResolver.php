<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Ast\Parser\PhpStanParser;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;

class PhpStanTypeResolver
{
    /**
     * @return list<string>
     */
    public static function resolveType(Expr|ComplexType|Name|Identifier|null $type, Scope $scope): array
    {
        if (null === $type || $type instanceof Expr) {
            return [];
        }

        return match (true) {
            $type instanceof Name => [$scope->resolveName($type)],
            $type instanceof Identifier => [],
            $type instanceof NullableType => self::resolveType($type->type, $scope),
            $type instanceof IntersectionType, $type instanceof UnionType => array_merge(...array_map(static fn ($type): array => self::resolveType($type, $scope), $type->types)),
            default => [],
        };
    }
}
