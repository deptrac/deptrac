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
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

/**
 * @implements NikicReferenceExtractorInterface<Node\Expr\Variable>
 * @implements PHPStanReferenceExtractorInterface<Node\Expr\Variable>
 */
final class VariableExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    /**
     * @var list<string>
     */
    private array $allowedNames;
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;

    public function __construct(
        private readonly PhpStanContainerDecorator $phpStanContainer,
        private readonly TypeResolverInterface $typeResolver,
    ) {
        $config = new ParserConfig(usedAttributes: ['lines' => true, 'indexes' => true]);
        $this->lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $this->docParser = new PhpDocParser($config, new TypeParser($config, $constExprParser), $constExprParser);
        $this->allowedNames = SuperGlobalToken::allowedNames();
    }

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        if (in_array($node->name, $this->allowedNames, true)) {
            /** @throws void */
            $referenceBuilder->dependency(SuperGlobalToken::from($node->name), $node->getLine(), DependencyType::SUPERGLOBAL_VARIABLE);
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

        foreach ($docNode->getVarTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);

            foreach ($types as $type) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($type), $docComment->getStartLine(), DependencyType::VARIABLE);
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
        Scope $scope,
    ): void {
        if (in_array($node->name, $this->allowedNames, true)) {
            /** @throws void */
            $referenceBuilder->dependency(SuperGlobalToken::from($node->name), $node->getLine(), DependencyType::SUPERGLOBAL_VARIABLE);
        }

        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return;
        }

        $fileTypeMapper = $this->phpStanContainer->createFileTypeMapper();
        /** @throws void */
        $resolvedPhpDoc = $fileTypeMapper->getResolvedPhpDoc(
            $scope->getFile(),
            $scope->getClassReflection()?->getName(),
            $scope->getTraitReflection()?->getName(),
            $scope->getFunction()?->getName(),
            $docComment->getText(),
        );

        foreach ($resolvedPhpDoc->getVarTags() as $tag) {
            foreach ($tag->getType()->getReferencedClasses() as $referencedClass) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($referencedClass), $docComment->getStartLine(), DependencyType::VARIABLE);
            }
        }
    }
}
