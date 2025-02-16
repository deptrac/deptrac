<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\Extractors;

use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Core\Ast\Parser\TypeScope;
use DEPTRAC_INTERNAL\PhpParser\Comment\Doc;
use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\ClassMethod;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Property;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Lexer\Lexer;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\ConstExprParser;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\PhpDocParser;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\TokenIterator;
use DEPTRAC_INTERNAL\PHPStan\PhpDocParser\Parser\TypeParser;
class AnnotationReferenceExtractor implements \Deptrac\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
{
    private readonly Lexer $lexer;
    private readonly PhpDocParser $docParser;
    public function __construct(private readonly TypeResolver $typeResolver)
    {
        $this->lexer = new Lexer();
        $this->docParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
    }
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void
    {
        if (!$node instanceof Property && !$node instanceof Node\Stmt\Expression && !$node instanceof ClassMethod) {
            return;
        }
        /**
         * @see https://github.com/nikic/PHP-Parser/commit/4e27a17cd855b36abe0199efb81be143b144f40d#diff-4034fc485172f50147405c293a9d86685b0f333e69b666de5492da37406186afL44 for the change in nikic/php-parser
         * @see https://github.com/patrickkusebauch/phpstan-src/commit/cc4bff635ebae19b010b81130360155692283ac6#diff-c4e3f0a39ea5d27cabb86159d23a29adbf4ba64b1931497f8a9bac2e720579d9R81 for the stolen implementation from PHPStan
         */
        if ($node instanceof Node\Stmt\Expression) {
            if (!$node->expr instanceof Node\Expr\Assign && !$node->expr instanceof Node\Expr\AssignRef) {
                return;
            }
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return;
        }
        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        $docNode = $this->docParser->parse($tokens);
        $templateTypes = \array_merge(\array_map(static fn(TemplateTagValueNode $templateNode): string => $templateNode->name, $docNode->getTemplateTagValues()), $referenceBuilder->getTokenTemplates());
        foreach ($docNode->getVarTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);
            foreach ($types as $type) {
                $referenceBuilder->variable($type, $docComment->getStartLine());
            }
        }
        foreach ($docNode->getParamTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);
            foreach ($types as $type) {
                $referenceBuilder->parameter($type, $docComment->getStartLine());
            }
        }
        foreach ($docNode->getReturnTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);
            foreach ($types as $type) {
                $referenceBuilder->returnType($type, $docComment->getStartLine());
            }
        }
        foreach ($docNode->getThrowsTagValues() as $tag) {
            $types = $this->typeResolver->resolvePHPStanDocParserType($tag->type, $typeScope, $templateTypes);
            foreach ($types as $type) {
                $referenceBuilder->throwStatement($type, $docComment->getStartLine());
            }
        }
    }
}
