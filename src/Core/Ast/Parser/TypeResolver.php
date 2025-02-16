<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser;

use InvalidArgumentException;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\FqsenResolver;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Type;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\TypeResolver as phpDocumentorTypeResolver;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Compound;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Context;
use DEPTRAC_INTERNAL\phpDocumentor\Reflection\Types\Object_;
use DEPTRAC_INTERNAL\PhpParser\Node\ComplexType;
use DEPTRAC_INTERNAL\PhpParser\Node\Identifier;
use DEPTRAC_INTERNAL\PhpParser\Node\IntersectionType;
use DEPTRAC_INTERNAL\PhpParser\Node\Name;
use DEPTRAC_INTERNAL\PhpParser\Node\Name\FullyQualified;
use DEPTRAC_INTERNAL\PhpParser\Node\NullableType;
use DEPTRAC_INTERNAL\PhpParser\Node\UnionType;
use DEPTRAC_INTERNAL\PhpParser\NodeAbstract;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Throwable;
class TypeResolver
{
    private readonly phpDocumentorTypeResolver $typeResolver;
    public function __construct()
    {
        $this->typeResolver = new phpDocumentorTypeResolver(new FqsenResolver());
    }
    /**
     * @return string[]
     */
    public function resolvePHPParserTypes(\Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, NodeAbstract ...$nodes) : array
    {
        $types = [];
        foreach ($nodes as $node) {
            $types[] = $this->resolvePHPParserType($typeScope, $node);
        }
        return \array_merge([], ...$types);
    }
    /**
     * @return string[]
     */
    private function resolvePHPParserType(\Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, NodeAbstract $node) : array
    {
        return match (\true) {
            $node instanceof Name && $node->isSpecialClassName() => [],
            $node instanceof Name => $this->resolveString($node->toCodeString(), $typeScope),
            $node instanceof NullableType => $this->resolvePHPParserType($typeScope, $node->type),
            $node instanceof UnionType => $this->resolvePHPParserTypes($typeScope, ...$node->types),
            $node instanceof IntersectionType => $this->resolvePHPParserTypes($typeScope, ...$node->types),
            default => [],
        };
    }
    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    public function resolvePHPStanDocParserType(TypeNode $type, \Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, array $templateTypes) : array
    {
        return match (\true) {
            $type instanceof IdentifierTypeNode => \in_array($type->name, $templateTypes, \true) ? [] : $this->resolveString($type->name, $typeScope),
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
    private function resolveString(string $type, \Deptrac\Deptrac\Core\Ast\Parser\TypeScope $nameScope) : array
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
    /**
     * @return string[]
     */
    public function resolvePropertyType(Identifier|Name|ComplexType $type) : array
    {
        return match (\true) {
            $type instanceof FullyQualified => [(string) $type],
            $type instanceof NullableType => $this->resolvePropertyType($type->type),
            $type instanceof UnionType || $type instanceof IntersectionType => \array_merge([], ...\array_map(fn(Identifier|Name|IntersectionType $typeNode): array => $this->resolvePropertyType($typeNode), $type->types)),
            default => [],
        };
    }
    /**
     * @return string[]
     */
    private function resolveReflectionType(Type $resolvedType) : array
    {
        return match (\true) {
            $resolvedType instanceof Object_ => ($fqsen = $resolvedType->getFqsen()) ? [(string) $fqsen] : [],
            $resolvedType instanceof Compound => \array_merge([], ...\array_map(fn(Type $type) => $this->resolveReflectionType($type), \iterator_to_array($resolvedType))),
            default => [],
        };
    }
    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveGeneric(GenericTypeNode $type, \Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, array $templateTypes) : array
    {
        $preType = 'list' === $type->type->name ? [] : $this->resolvePHPStanDocParserType($type->type, $typeScope, $templateTypes);
        return \array_merge($preType, ...\array_map(fn(TypeNode $typeNode): array => $this->resolvePHPStanDocParserType($typeNode, $typeScope, $templateTypes), $type->genericTypes));
    }
    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveCallable(CallableTypeNode $type, \Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, array $templateTypes) : array
    {
        return \array_merge($this->resolvePHPStanDocParserType($type->returnType, $typeScope, $templateTypes), ...\array_map(fn(CallableTypeParameterNode $parameterNode): array => $this->resolvePHPStanDocParserType($parameterNode->type, $typeScope, $templateTypes), $type->parameters));
    }
    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveArray(ArrayShapeNode $type, \Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, array $templateTypes) : array
    {
        return \array_merge([], ...\array_map(fn(ArrayShapeItemNode $itemNode): array => $this->resolvePHPStanDocParserType($itemNode->valueType, $typeScope, $templateTypes), $type->items));
    }
    /**
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    private function resolveVariableType(UnionTypeNode|IntersectionTypeNode $type, \Deptrac\Deptrac\Core\Ast\Parser\TypeScope $typeScope, array $templateTypes) : array
    {
        return \array_merge([], ...\array_map(fn(TypeNode $typeNode): array => $this->resolvePHPStanDocParserType($typeNode, $typeScope, $templateTypes), $type->types));
    }
}
