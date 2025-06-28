<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\SuperGlobalToken;
use Deptrac\Deptrac\Contract\Ast\ReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;

/**
 * @implements ReferenceExtractorInterface<\PhpParser\Node\Expr\Variable>
 */
final class VariableExtractor implements ReferenceExtractorInterface
{
    /**
     * @var list<string>
     */
    private array $allowedNames;
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;

    public function __construct(
        private readonly TypeResolverInterface $typeResolver,
    ) {
        $this->lexer = new Lexer();
        $this->docParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $this->allowedNames = SuperGlobalToken::allowedNames();
    }

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        if (in_array($node->name, $this->allowedNames, true)) {
            /** @throws void */
            $referenceBuilder->dependency(SuperGlobalToken::from($node->name), $node->getLine(), DependencyType::SUPERGLOBAL_VARIABLE);
        }

        $docComment = $node->getDocComment();
        if ($docComment instanceof Doc) {
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
    }

    public function getNodeType(): string
    {
        return Node\Expr\Variable::class;
    }
}
