<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\Composer\Pcre\PHPStan;

use DEPTRAC_INTERNAL\Composer\Pcre\Preg;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\StaticCall;
use DEPTRAC_INTERNAL\PHPStan\Analyser\Scope;
use DEPTRAC_INTERNAL\PHPStan\Reflection\MethodReflection;
use DEPTRAC_INTERNAL\PHPStan\Reflection\ParameterReflection;
use DEPTRAC_INTERNAL\PHPStan\TrinaryLogic;
use DEPTRAC_INTERNAL\PHPStan\Type\Php\RegexArrayShapeMatcher;
use DEPTRAC_INTERNAL\PHPStan\Type\StaticMethodParameterOutTypeExtension;
use DEPTRAC_INTERNAL\PHPStan\Type\Type;
final class PregMatchParameterOutTypeExtension implements StaticMethodParameterOutTypeExtension
{
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;
    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }
    public function isStaticMethodSupported(MethodReflection $methodReflection, ParameterReflection $parameter) : bool
    {
        return $methodReflection->getDeclaringClass()->getName() === Preg::class && \in_array($methodReflection->getName(), ['match', 'isMatch', 'matchStrictGroups', 'isMatchStrictGroups', 'matchAll', 'isMatchAll', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true) && $parameter->getName() === 'matches';
    }
    public function getParameterOutTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, ParameterReflection $parameter, Scope $scope) : ?Type
    {
        $args = $methodCall->getArgs();
        $patternArg = $args[0] ?? null;
        $matchesArg = $args[2] ?? null;
        $flagsArg = $args[3] ?? null;
        if ($patternArg === null || $matchesArg === null) {
            return null;
        }
        $flagsType = PregMatchFlags::getType($flagsArg, $scope);
        if ($flagsType === null) {
            return null;
        }
        if (\stripos($methodReflection->getName(), 'matchAll') !== \false) {
            return $this->regexShapeMatcher->matchAllExpr($patternArg->value, $flagsType, TrinaryLogic::createMaybe(), $scope);
        }
        return $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createMaybe(), $scope);
    }
}
