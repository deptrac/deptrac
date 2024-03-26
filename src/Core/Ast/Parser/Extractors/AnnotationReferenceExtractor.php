<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser\Extractors;

use DEPTRAC_202404\PhpParser\Comment\Doc;
use DEPTRAC_202404\PhpParser\Node;
use DEPTRAC_202404\PhpParser\Node\Expr\Variable;
use DEPTRAC_202404\PhpParser\Node\Stmt\ClassMethod;
use DEPTRAC_202404\PhpParser\Node\Stmt\Property;
use DEPTRAC_202404\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use DEPTRAC_202404\PHPStan\PhpDocParser\Lexer\Lexer;
use DEPTRAC_202404\PHPStan\PhpDocParser\Parser\ConstExprParser;
use DEPTRAC_202404\PHPStan\PhpDocParser\Parser\PhpDocParser;
use DEPTRAC_202404\PHPStan\PhpDocParser\Parser\TokenIterator;
use DEPTRAC_202404\PHPStan\PhpDocParser\Parser\TypeParser;
use Qossmic\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Qossmic\Deptrac\Core\Ast\Parser\TypeResolver;
use Qossmic\Deptrac\Core\Ast\Parser\TypeScope;
class AnnotationReferenceExtractor implements \Qossmic\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
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
        if (!$node instanceof Property && !$node instanceof Variable && !$node instanceof ClassMethod) {
            return;
        }
        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return;
        }
        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        $docNode = $this->docParser->parse($tokens);
        $templateTypes = \array_merge(\array_map(static fn(TemplateTagValueNode $node): string => $node->name, $docNode->getTemplateTagValues()), $referenceBuilder->getTokenTemplates());
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
