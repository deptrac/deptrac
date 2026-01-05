<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use InvalidArgumentException;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver as phpDocumentorTypeResolver;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Object_;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PhpParser\NodeAbstract;
use PHPStan\Analyser\MutatingScope;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Throwable;

class TypeResolver implements TypeResolverInterface
{
    private readonly phpDocumentorTypeResolver $typeResolver;

    public function __construct()
    {
        $this->typeResolver = new phpDocumentorTypeResolver(new FqsenResolver());
    }

    public static function resolveType(Expr|ComplexType|Name|Identifier|null $type, MutatingScope $scope): array
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

    public function resolvePHPParserTypes(TypeScope $typeScope, NodeAbstract ...$nodes): array
    {
        $types = [];
        foreach ($nodes as $node) {
            $types[] = $this->resolvePHPParserType($typeScope, $node);
        }

        return array_merge([], ...$types);
    }

    /**
     * @return string[]
     */
    private function resolvePHPParserType(TypeScope $typeScope, NodeAbstract $node): array
    {
        return match (true) {
            $node instanceof Name && $node->isSpecialClassName() => [],
            $node instanceof Name => $this->resolveString($node->toCodeString(), $typeScope),
            $node instanceof NullableType => $this->resolvePHPParserType($typeScope, $node->type),
            $node instanceof UnionType => $this->resolvePHPParserTypes($typeScope, ...$node->types),
            $node instanceof IntersectionType => $this->resolvePHPParserTypes($typeScope, ...$node->types),
            default => [],
        };
    }

    public function resolvePHPStanDocParserType(TypeNode $type, TypeScope $typeScope, array $templateTypes): array
    {
        return match (true) {
            $type instanceof IdentifierTypeNode => in_array($type->name, $templateTypes, true) ? [] : $this->resolveString($type->name, $typeScope),
            $type instanceof ConstTypeNode && $type->constExpr instanceof ConstFetchNode => $this->resolveString($type->constExpr->className, $typeScope),
            $type instanceof NullableTypeNode => $this->resolvePHPStanDocParserType($type->type, $typeScope, $templateTypes),
            $type instanceof ArrayTypeNode => $this->resolvePHPStanDocParserType($type->type, $typeScope, $templateTypes),
            $type instanceof UnionTypeNode || $type instanceof IntersectionTypeNode => $this->resolveVariableType($type, $typeScope, $templateTypes),
            $type instanceof GenericTypeNode => $this->resolveGeneric($type, $typeScope, $templateTypes),
            $type instanceof ArrayShapeNode => $this->resolveArray($type, $typeScope, $templateTypes),
            $type instanceof CallableTypeNode => $this->resolveCallable($type, $typeScope, $templateTypes),
            default => $this->resolveString((string) $type, $typeScope),
        };
    }

    /**
     * @return string[]
     */
    private function resolveString(string $type, TypeScope $nameScope): array
    {
        try {
            $context = new Context($nameScope->namespace, $nameScope->getUses());
            /** @throws InvalidArgumentException */
            $resolvedType = $this->typeResolver->resolve($type, $context);

            return $this->resolveReflectionType($resolvedType);
        } catch (Throwable) {
            return [];
        }
    }

    public function resolvePropertyType(Identifier|Name|ComplexType $type): array
    {
        return match (true) {
            $type instanceof FullyQualified => [(string) $type],
            $type instanceof NullableType => $this->resolvePropertyType($type->type),
            $type instanceof UnionType || $type instanceof IntersectionType => array_merge(
                [],
                ...array_map(
                    $this->resolvePropertyType(...),
                    $type->types
                )
            ),
            default => [],
        };
    }

    /**
     * @return string[]
     */
    private function resolveReflectionType(Type $resolvedType): array
    {
        return match (true) {
            $resolvedType instanceof Object_ => ($fqsen = $resolvedType->getFqsen()) ? [(string) $fqsen] : [],
            $resolvedType instanceof Compound => array_merge([], ...array_map($this->resolveReflectionType(...), iterator_to_array($resolvedType))),
            default => [],
        };
    }

    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveGeneric(GenericTypeNode $type, TypeScope $typeScope, array $templateTypes): array
    {
        /** Generic int (e.g. int<0, max>) can never contain resolvable generic type, so only resolve the `int` */
        if ('int' === $type->type->name) {
            return $this->resolvePHPStanDocParserType(
                $type->type,
                $typeScope,
                $templateTypes
            );
        }

        /** Does the identifier of generic (e.g. list in list<Class>) need resolving? */
        $preType = in_array($type->type->name, ['list', 'non-empty-list'], true)
            ? []
            : $this->resolvePHPStanDocParserType(
                $type->type,
                $typeScope,
                $templateTypes
            );

        return array_merge(
            $preType,
            ...array_map(
                fn (TypeNode $typeNode): array => $this->resolvePHPStanDocParserType(
                    $typeNode,
                    $typeScope,
                    $templateTypes
                ),
                $type->genericTypes
            )
        );
    }

    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveCallable(CallableTypeNode $type, TypeScope $typeScope, array $templateTypes): array
    {
        return array_merge(
            $this->resolvePHPStanDocParserType($type->returnType, $typeScope, $templateTypes),
            ...array_map(
                fn (CallableTypeParameterNode $parameterNode): array => $this->resolvePHPStanDocParserType(
                    $parameterNode->type,
                    $typeScope,
                    $templateTypes
                ),
                $type->parameters
            )
        );
    }

    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveArray(ArrayShapeNode $type, TypeScope $typeScope, array $templateTypes): array
    {
        return array_merge([],
            ...array_map(
                fn (ArrayShapeItemNode $itemNode): array => $this->resolvePHPStanDocParserType(
                    $itemNode->valueType,
                    $typeScope,
                    $templateTypes
                ),
                $type->items
            ));
    }

    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveVariableType(
        UnionTypeNode|IntersectionTypeNode $type,
        TypeScope $typeScope,
        array $templateTypes,
    ): array {
        return array_merge([],
            ...array_map(
                fn (TypeNode $typeNode): array => $this->resolvePHPStanDocParserType(
                    $typeNode,
                    $typeScope,
                    $templateTypes
                ),
                $type->types
            ));
    }
}
