<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL\Composer\Pcre\PHPStan;

use DEPTRAC_INTERNAL\Composer\Pcre\Preg;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\StaticCall;
use DEPTRAC_INTERNAL\PHPStan\Analyser\Scope;
use DEPTRAC_INTERNAL\PHPStan\Analyser\SpecifiedTypes;
use DEPTRAC_INTERNAL\PHPStan\Analyser\TypeSpecifier;
use DEPTRAC_INTERNAL\PHPStan\Analyser\TypeSpecifierAwareExtension;
use DEPTRAC_INTERNAL\PHPStan\Analyser\TypeSpecifierContext;
use DEPTRAC_INTERNAL\PHPStan\Reflection\MethodReflection;
use DEPTRAC_INTERNAL\PHPStan\TrinaryLogic;
use DEPTRAC_INTERNAL\PHPStan\Type\Constant\ConstantArrayType;
use DEPTRAC_INTERNAL\PHPStan\Type\Php\RegexArrayShapeMatcher;
use DEPTRAC_INTERNAL\PHPStan\Type\StaticMethodTypeSpecifyingExtension;
use DEPTRAC_INTERNAL\PHPStan\Type\TypeCombinator;
use DEPTRAC_INTERNAL\PHPStan\Type\Type;
final class PregMatchTypeSpecifyingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    /**
     * @var TypeSpecifier
     */
    private $typeSpecifier;
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;
    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier) : void
    {
        $this->typeSpecifier = $typeSpecifier;
    }
    public function getClass() : string
    {
        return Preg::class;
    }
    public function isStaticMethodSupported(MethodReflection $methodReflection, StaticCall $node, TypeSpecifierContext $context) : bool
    {
        return \in_array($methodReflection->getName(), ['match', 'isMatch', 'matchStrictGroups', 'isMatchStrictGroups', 'matchAll', 'isMatchAll', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true) && !$context->null();
    }
    public function specifyTypes(MethodReflection $methodReflection, StaticCall $node, Scope $scope, TypeSpecifierContext $context) : SpecifiedTypes
    {
        $args = $node->getArgs();
        $patternArg = $args[0] ?? null;
        $matchesArg = $args[2] ?? null;
        $flagsArg = $args[3] ?? null;
        if ($patternArg === null || $matchesArg === null) {
            return new SpecifiedTypes();
        }
        $flagsType = PregMatchFlags::getType($flagsArg, $scope);
        if ($flagsType === null) {
            return new SpecifiedTypes();
        }
        if (\stripos($methodReflection->getName(), 'matchAll') !== \false) {
            $matchedType = $this->regexShapeMatcher->matchAllExpr($patternArg->value, $flagsType, TrinaryLogic::createFromBoolean($context->true()), $scope);
        } else {
            $matchedType = $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createFromBoolean($context->true()), $scope);
        }
        if ($matchedType === null) {
            return new SpecifiedTypes();
        }
        if (\in_array($methodReflection->getName(), ['matchStrictGroups', 'isMatchStrictGroups', 'matchAllStrictGroups', 'isMatchAllStrictGroups'], \true)) {
            $matchedType = PregMatchFlags::removeNullFromMatches($matchedType);
        }
        $overwrite = \false;
        if ($context->false()) {
            $overwrite = \true;
            $context = $context->negate();
        }
        // @phpstan-ignore function.alreadyNarrowedType
        if (\method_exists('DEPTRAC_INTERNAL\\PHPStan\\Analyser\\SpecifiedTypes', 'setRootExpr')) {
            $typeSpecifier = $this->typeSpecifier->create($matchesArg->value, $matchedType, $context, $scope)->setRootExpr($node);
            return $overwrite ? $typeSpecifier->setAlwaysOverwriteTypes() : $typeSpecifier;
        }
        // @phpstan-ignore arguments.count
        return $this->typeSpecifier->create(
            $matchesArg->value,
            $matchedType,
            $context,
            // @phpstan-ignore argument.type
            $overwrite,
            $scope,
            $node
        );
    }
}
