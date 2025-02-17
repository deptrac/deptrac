<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\Extractors;

use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\Instanceof_;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\New_;
use DEPTRAC_INTERNAL\PhpParser\Node\Name;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Catch_;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Core\Ast\Parser\TypeScope;
class KeywordExtractor implements \Deptrac\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
{
    public function __construct(private readonly TypeResolver $typeResolver)
    {
    }
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void
    {
        if ($node instanceof Node\Stmt\TraitUse && $referenceBuilder instanceof ClassLikeReferenceBuilder) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, ...$node->traits) as $classLikeName) {
                $referenceBuilder->trait($classLikeName, $node->getLine());
            }
            return;
        }
        if ($node instanceof Instanceof_ && $node->class instanceof Name) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->class) as $classLikeName) {
                $referenceBuilder->instanceof($classLikeName, $node->class->getLine());
            }
            return;
        }
        if ($node instanceof New_ && $node->class instanceof Name) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->class) as $classLikeName) {
                $referenceBuilder->newStatement($classLikeName, $node->class->getLine());
            }
            return;
        }
        if ($node instanceof Catch_) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, ...$node->types) as $classLikeName) {
                $referenceBuilder->catchStmt($classLikeName, $node->getLine());
            }
        }
    }
}
