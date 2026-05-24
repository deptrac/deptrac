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
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\MutatingScope;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

/**
 * @implements NikicReferenceExtractorInterface<ClassLike>
 * @implements PHPStanReferenceExtractorInterface<ClassLike>
 */
final readonly class ClassLikeExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    private Lexer $lexer;
    private PhpDocParser $docParser;

    public function __construct(
        private PhpStanContainerDecorator $phpStanContainer,
        private TypeResolverInterface $typeResolver,
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

        $resolved = DocParsingHelper::resolvePHPDocWithNativeScope($node, $this->lexer, $this->docParser, $referenceBuilder->getTokenTemplateLikes());
        if (null === $resolved) {
            return;
        }
        [$docNode, $templateTypes] = $resolved;

        foreach ($docNode->getMethodTagValues() as $methodTagValue) {
            foreach ($methodTagValue->parameters as $tag) {
                if (null !== $tag->type) {
                    $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

                    foreach ($types as $type) {
                        $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::PARAMETER);
                    }
                }
            }
            $returnType = $methodTagValue->returnType;
            if (null !== $returnType) {
                $types = $this->typeResolver->resolvePHPStanDocParserType($returnType, $typeScope, $templateTypes);

                foreach ($types as $type) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::RETURN_TYPE);
                }
            }
        }

        /** @var list<PropertyTagValueNode> $propertyTags */
        $propertyTags = array_merge($docNode->getPropertyTagValues(), $docNode->getPropertyReadTagValues(), $docNode->getPropertyWriteTagValues());
        foreach ($propertyTags as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

            foreach ($types as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::VARIABLE);
            }
        }
    }

    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                foreach ($this->typeResolver->resolveType($attribute->name, $scope) as $classLikeName) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $attribute->getLine(), DependencyType::ATTRIBUTE);
                }
            }
        }

        $resolvedPhpDoc = DocParsingHelper::resolvePHPDocWithPHPStanScope($node, $this->phpStanContainer, $scope);
        if (null === $resolvedPhpDoc) {
            return;
        }

        foreach ($resolvedPhpDoc->getMethodTags() as $methodTag) {
            foreach ($methodTag->getParameters() as $methodTagParameter) {
                foreach ($methodTagParameter->getType()->getReferencedClasses() as $referencedClass) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::PARAMETER);
                }
            }
            foreach ($methodTag->getReturnType()->getReferencedClasses() as $referencedClass) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::RETURN_TYPE);
            }
        }

        foreach ($resolvedPhpDoc->getPropertyTags() as $propertyTag) {
            $referencedClasses = array_merge(
                $propertyTag->getReadableType()?->getReferencedClasses() ?? [],
                $propertyTag->getWritableType()?->getReferencedClasses() ?? [],
            );
            foreach (array_unique($referencedClasses) as $referencedClass) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::VARIABLE);
            }
        }
    }
}
