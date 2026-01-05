<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use Deptrac\Deptrac\DefaultBehavior\Ast\DocParsingHelper;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;

/**
 * @implements NikicReferenceExtractorInterface<Node\Expr\Variable>
 * @implements PHPStanReferenceExtractorInterface<Node\Expr\Variable>
 */
final class VariableExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    /**
     * @var list<string>
     */
    private readonly array $allowedNames;
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;

    public function __construct(
        private readonly PhpStanContainerDecorator $phpStanContainer,
        private readonly TypeResolverInterface $typeResolver,
    ) {
        [$this->lexer, $this->docParser] = DocParsingHelper::create();
        $this->allowedNames = SuperGlobalToken::allowedNames();
    }

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        if (in_array($node->name, $this->allowedNames, true)) {
            /** @throws void */
            $referenceBuilder->dependency(SuperGlobalToken::from($node->name), $node->getLine(), DependencyType::SUPERGLOBAL_VARIABLE);
        }

        $resolved = DocParsingHelper::resolvePHPDocWithNativeScope($node, $this->lexer, $this->docParser, $referenceBuilder->getTokenTemplateLikes());
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
        return Node\Expr\Variable::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        if (in_array($node->name, $this->allowedNames, true)) {
            /** @throws void */
            $referenceBuilder->dependency(SuperGlobalToken::from($node->name), $node->getLine(), DependencyType::SUPERGLOBAL_VARIABLE);
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
