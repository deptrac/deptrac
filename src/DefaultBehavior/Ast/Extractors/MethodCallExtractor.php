<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\ClassLikeReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\ClassMethodReferenceBuilder;
use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\MutatingScope;
use Throwable;

/**
 * Emits method-token dependencies for method calls.
 *
 * Intra-class dispatch ($this->method(), self::method(), static::method(),
 * including their first-class callable forms) is tracked with both parsers.
 * With the PHPStan parser, calls on receivers whose type is statically known
 * from class context (typed properties, new expressions, return-type chains)
 * are additionally resolved to the target class's method token.
 *
 * Known limitations: dynamic method names are skipped; parent::method() stays
 * untracked because the parent class is not known at extraction time; calls on
 * $this inside methods of anonymous classes are attributed to the enclosing
 * named class; receivers typed only by local variables or parameters are not
 * resolved (the PHPStan scope is class-level, without flow analysis);
 * cross-class static calls stay tracked at class level only (they already emit
 * a static_method dependency).
 *
 * @implements NikicReferenceExtractorInterface<CallLike>
 * @implements PHPStanReferenceExtractorInterface<CallLike>
 */
final class MethodCallExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(private readonly bool $methodGranularity = true) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        if (!$this->methodGranularity) {
            return;
        }

        $this->resolveSelfDispatch($node, $referenceBuilder);
    }

    public function processNodeWithPhpStanScope(Node $node, ReferenceBuilderInterface $referenceBuilder, MutatingScope $scope): void
    {
        if (!$this->methodGranularity) {
            return;
        }

        $this->resolveSelfDispatch($node, $referenceBuilder);
        $this->resolveCrossClassDispatch($node, $referenceBuilder, $scope);
    }

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    private function resolveSelfDispatch(CallLike $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        $classToken = match (true) {
            $referenceBuilder instanceof ClassMethodReferenceBuilder => $referenceBuilder->getMethodToken()->getClassToken(),
            $referenceBuilder instanceof ClassLikeReferenceBuilder => $referenceBuilder->getClassToken(),
            default => null,
        };

        if (null === $classToken) {
            return;
        }

        $methodName = $this->selfDispatchedMethodName($node);

        if (null === $methodName) {
            return;
        }

        $referenceBuilder->dependency(
            ClassMethodToken::fromClassTokenAndMethodName($classToken, $methodName),
            $node->getLine(),
            DependencyType::METHOD_CALL
        );
    }

    private function resolveCrossClassDispatch(CallLike $node, ReferenceBuilderInterface $referenceBuilder, MutatingScope $scope): void
    {
        // cross-class edges belong to the calling method
        if (!$referenceBuilder instanceof ClassMethodReferenceBuilder) {
            return;
        }

        if (!$node instanceof MethodCall && !$node instanceof NullsafeMethodCall) {
            return;
        }

        if (!$node->name instanceof Identifier) {
            return;
        }

        // $this receivers are covered by the self-dispatch path
        if ($node->var instanceof Variable && 'this' === $node->var->name) {
            return;
        }

        foreach ($this->resolveReceiverClassNames($node->var, $scope) as $className) {
            $referenceBuilder->dependency(
                ClassMethodToken::fromFQCNAndMethodName($className, $node->name->toString()),
                $node->getLine(),
                DependencyType::METHOD_CALL
            );
        }
    }

    /**
     * @return list<string>
     */
    private function resolveReceiverClassNames(Node\Expr $receiver, MutatingScope $scope): array
    {
        try {
            // the class-level scope has no $this variable, but the type of a
            // property fetched on $this is known from the class reflection
            if ($receiver instanceof Node\Expr\PropertyFetch
                && $receiver->var instanceof Variable
                && 'this' === $receiver->var->name
                && $receiver->name instanceof Identifier
            ) {
                $classReflection = $scope->getClassReflection();
                $propertyName = $receiver->name->toString();

                if (null === $classReflection || !$classReflection->hasProperty($propertyName)) {
                    return [];
                }

                return $classReflection->getProperty($propertyName, $scope)->getReadableType()->getObjectClassNames();
            }

            return $scope->getType($receiver)->getObjectClassNames();
            // @phpstan-ignore catch.neverThrown (getType() throws at runtime for variables unknown to the class-level scope)
        } catch (Throwable) {
            return [];
        }
    }

    private function selfDispatchedMethodName(CallLike $node): ?string
    {
        if ($node instanceof MethodCall
            && $node->var instanceof Variable
            && 'this' === $node->var->name
            && $node->name instanceof Identifier
        ) {
            return $node->name->toString();
        }

        if ($node instanceof StaticCall
            && $node->class instanceof Name
            && in_array($node->class->toLowerString(), ['self', 'static'], true)
            && $node->name instanceof Identifier
        ) {
            return $node->name->toString();
        }

        return null;
    }
}
