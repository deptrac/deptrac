<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use Deptrac\Deptrac\Core\Ast\Parser\PhpStanParser\PhpStanTypeResolver;
use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @implements NikicReferenceExtractorInterface<Node\FunctionLike>
 * @implements PHPStanReferenceExtractorInterface<Node\FunctionLike>
 */
final class FunctionLikeExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(
        private readonly PhpStanTypeResolver $phpStanTypeResolver,
        private readonly TypeResolverInterface $typeResolver,
    ) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($node->getAttrGroups() as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $attribute->name) as $classLikeName) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $attribute->getLine(), DependencyType::ATTRIBUTE);
                }
            }
        }
        foreach ($node->getParams() as $param) {
            if (null !== $param->type) {
                foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $param->type) as $classLikeName) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $param->type->getLine(), DependencyType::PARAMETER);
                }
            }
            foreach ($param->attrGroups as $attrGroup) {
                foreach ($attrGroup->attrs as $attribute) {
                    foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $attribute->name) as $classLikeName) {
                        $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $attribute->getLine(), DependencyType::ATTRIBUTE);
                    }
                }
            }
        }
        $returnType = $node->getReturnType();
        if (null !== $returnType) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $returnType) as $classLikeName) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $returnType->getLine(), DependencyType::RETURN_TYPE);
            }
        }
    }

    public function getNodeType(): string
    {
        return Node\FunctionLike::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        Scope $scope
    ): void {
        foreach ($node->getAttrGroups() as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($attribute->name)), $attribute->getLine(), DependencyType::ATTRIBUTE);
            }
        }
        foreach ($node->getParams() as $param) {
            if (null !== $param->type) {
                foreach ($this->phpStanTypeResolver->resolveType($param->type, $scope) as $item) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($item), $param->type->getLine(), DependencyType::PARAMETER);
                }
            }
            foreach ($param->attrGroups as $attrGroup) {
                foreach ($attrGroup->attrs as $attribute) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($attribute->name)), $attribute->getLine(), DependencyType::ATTRIBUTE);
                }
            }
        }

        $returnType = $node->getReturnType();
        foreach ($this->phpStanTypeResolver->resolveType($returnType, $scope) as $item) {
            assert(null !== $returnType);
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($item), $returnType->getLine(), DependencyType::RETURN_TYPE);
        }
    }
}
