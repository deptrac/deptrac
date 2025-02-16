<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\Composer\Pcre\PHPStan;

use DEPTRAC_INTERNAL\PHPStan\Analyser\Scope;
use DEPTRAC_INTERNAL\PHPStan\Type\ArrayType;
use DEPTRAC_INTERNAL\PHPStan\Type\Constant\ConstantArrayType;
use DEPTRAC_INTERNAL\PHPStan\Type\Constant\ConstantIntegerType;
use DEPTRAC_INTERNAL\PHPStan\Type\IntersectionType;
use DEPTRAC_INTERNAL\PHPStan\Type\TypeCombinator;
use DEPTRAC_INTERNAL\PHPStan\Type\Type;
use DEPTRAC_INTERNAL\PhpParser\Node\Arg;
use DEPTRAC_INTERNAL\PHPStan\Type\Php\RegexArrayShapeMatcher;
use DEPTRAC_INTERNAL\PHPStan\Type\TypeTraverser;
use DEPTRAC_INTERNAL\PHPStan\Type\UnionType;
final class PregMatchFlags
{
    public static function getType(?Arg $flagsArg, Scope $scope) : ?Type
    {
        if ($flagsArg === null) {
            return new ConstantIntegerType(\PREG_UNMATCHED_AS_NULL);
        }
        $flagsType = $scope->getType($flagsArg->value);
        $constantScalars = $flagsType->getConstantScalarValues();
        if ($constantScalars === []) {
            return null;
        }
        $internalFlagsTypes = [];
        foreach ($flagsType->getConstantScalarValues() as $constantScalarValue) {
            if (!\is_int($constantScalarValue)) {
                return null;
            }
            $internalFlagsTypes[] = new ConstantIntegerType($constantScalarValue | \PREG_UNMATCHED_AS_NULL);
        }
        return TypeCombinator::union(...$internalFlagsTypes);
    }
    public static function removeNullFromMatches(Type $matchesType) : Type
    {
        return TypeTraverser::map($matchesType, static function (Type $type, callable $traverse) : Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }
            if ($type instanceof ConstantArrayType) {
                return new ConstantArrayType($type->getKeyTypes(), \array_map(static function (Type $valueType) use($traverse) : Type {
                    return $traverse($valueType);
                }, $type->getValueTypes()), $type->getNextAutoIndexes(), [], $type->isList());
            }
            if ($type instanceof ArrayType) {
                return new ArrayType($type->getKeyType(), $traverse($type->getItemType()));
            }
            return TypeCombinator::removeNull($type);
        });
    }
}
