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
use Deptrac\Deptrac\DefaultBehavior\Ast\DocParsingHelper;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\MutatingScope;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

/**
 * @implements NikicReferenceExtractorInterface<Property>
 * @implements PHPStanReferenceExtractorInterface<Property>
 */
final class PropertyExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;

    public function __construct(
        private readonly PhpStanContainerDecorator $phpStanContainer,
        private readonly TypeResolverInterface $typeResolver,
    ) {
        [$this->lexer, $this->docParser] = DocParsingHelper::create();
    }

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $attribute->name) as $classLikeName) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $attribute->getLine(), DependencyType::ATTRIBUTE);
                }
            }
        }
        if (null !== $node->type) {
            foreach ($this->typeResolver->resolvePropertyType($node->type) as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->type->getStartLine(), DependencyType::VARIABLE);
            }
        }

        $resolved = DocParsingHelper::resolvePHPDocWithNativeScope($node, $this->lexer, $this->docParser, $referenceBuilder->getTokenTemplates());
        if (null === $resolved) {
            return;
        }
        [$docNode, $templateTypes] = $resolved;

        foreach ($docNode->getVarTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

            foreach ($types as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::VARIABLE);
            }
        }
    }

    public function getNodeType(): string
    {
        return Property::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($attribute->name)), $attribute->getLine(), DependencyType::ATTRIBUTE);
            }
        }

        if ($node->type instanceof Node\Name) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($node->type)), $node->type->getStartLine(), DependencyType::VARIABLE);
        }

        $resolvedPhpDoc = DocParsingHelper::resolvePHPDocWithPHPStanScope($node, $this->phpStanContainer, $scope);
        if (null === $resolvedPhpDoc) {
            return;
        }

        foreach ($resolvedPhpDoc->getVarTags() as $tag) {
            foreach ($tag->getType()->getReferencedClasses() as $referencedClass) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::VARIABLE);
            }
        }
    }
}
