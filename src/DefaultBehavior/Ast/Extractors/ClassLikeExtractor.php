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
use Deptrac\Deptrac\Core\Ast\Parser\PhpStanParser\PhpStanContainerDecorator;
use Deptrac\Deptrac\Core\Ast\Parser\PhpStanParser\PhpStanTypeResolver;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * @implements NikicReferenceExtractorInterface<ClassLike>
 * @implements PHPStanReferenceExtractorInterface<ClassLike>
 */
final class ClassLikeExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;

    public function __construct(
        private readonly PhpStanContainerDecorator $phpStanContainer,
        private readonly PhpStanTypeResolver $phpStanTypeResolver,
        private readonly TypeResolverInterface $typeResolver,
    ) {
        $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
        $this->lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $this->docParser = new PhpDocParser($config, new TypeParser($config, $constExprParser), $constExprParser);
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

        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return;
        }

        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        $docNode = $this->docParser->parse($tokens);
        $templateTypes = array_merge(
            array_map(
                static fn (TemplateTagValueNode $node): string => $node->name,
                $docNode->getTemplateTagValues()
            ),
            $referenceBuilder->getTokenTemplates()
        );

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
        Scope $scope
    ): void {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                foreach ($this->phpStanTypeResolver->resolveType($attribute->name, $scope) as $classLikeName) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $attribute->getLine(), DependencyType::ATTRIBUTE);
                }
            }
        }

        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return;
        }

        $fileTypeMapper = $this->phpStanContainer->createFileTypeMapper();
        $classReflection = $scope->getClassReflection();
        assert(null !== $classReflection);

        /** @throws void */
        $resolvedPhpDoc = $fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $classReflection->getName(),
            $scope->getTraitReflection()?->getName(),
            $scope->getFunction()?->getName(),
            $docComment->getText(),
        );

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
