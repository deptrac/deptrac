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
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\MutatingScope;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

/**
 * @implements NikicReferenceExtractorInterface<ClassMethod>
 * @implements PHPStanReferenceExtractorInterface<ClassMethod>
 */
final class ClassMethodExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
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
        $resolved = DocParsingHelper::resolvePHPDocWithNativeScope($node, $this->lexer, $this->docParser, $referenceBuilder->getTokenTemplateLikes());
        if (null === $resolved) {
            return;
        }
        [$docNode, $templateTypes] = $resolved;

        foreach ($docNode->getParamTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

            foreach ($types as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::PARAMETER);
            }
        }

        foreach ($docNode->getReturnTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

            foreach ($types as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::RETURN_TYPE);
            }
        }

        foreach ($docNode->getThrowsTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

            foreach ($types as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::THROW);
            }
        }
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        $resolvedPhpDoc = DocParsingHelper::resolvePHPDocWithPHPStanScope($node, $this->phpStanContainer, $scope);
        if (null === $resolvedPhpDoc) {
            return;
        }

        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            // Traits have no class reflection in scope — PHPStan's resolved
            // PHPDoc is unreliable without a class context. Fall back to the
            // nikic-based PHPDoc parsing which is always stable.
            $resolved = DocParsingHelper::resolvePHPDocWithNativeScope($node, $this->lexer, $this->docParser, $referenceBuilder->getTokenTemplateLikes());
            if (null === $resolved) {
                return;
            }
            [$docNode, $templateTypes] = $resolved;

            foreach ($docNode->getParamTagValues() as $tag) {
                $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, new TypeScope(''), $templateTypes);
                foreach ($types as $type) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::PARAMETER);
                }
            }

            foreach ($docNode->getReturnTagValues() as $tag) {
                $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, new TypeScope(''), $templateTypes);
                foreach ($types as $type) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::RETURN_TYPE);
                }
            }

            foreach ($docNode->getThrowsTagValues() as $tag) {
                $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, new TypeScope(''), $templateTypes);
                foreach ($types as $type) {
                    $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $node->getStartLine(), DependencyType::THROW);
                }
            }

            return;
        }
        $methodVariant = $classReflection
            ->getMethod($node->name->name, $scope)
            ->getVariants()[0]
        ;

        foreach ($methodVariant->getParameters() as $tag) {
            foreach ($tag->getType()->getReferencedClasses() as $referencedClass) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::PARAMETER);
            }
        }

        foreach ($methodVariant->getPhpDocReturnType()->getReferencedClasses() as $referencedClass) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::RETURN_TYPE);
        }

        foreach ($resolvedPhpDoc->getThrowsTag()?->getType()->getReferencedClasses() ?? [] as $referencedClass) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $node->getStartLine(), DependencyType::THROW);
        }
    }
}
