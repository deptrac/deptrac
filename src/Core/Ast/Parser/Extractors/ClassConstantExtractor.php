<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser\Extractors;

use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\ClassConstFetch;
use DEPTRAC_INTERNAL\PhpParser\Node\Name;
use Qossmic\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Qossmic\Deptrac\Core\Ast\Parser\TypeScope;
class ClassConstantExtractor implements \Qossmic\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void
    {
        if (!$node instanceof ClassConstFetch || !$node->class instanceof Name || $node->class->isSpecialClassName()) {
            return;
        }
        $referenceBuilder->constFetch($node->class->toCodeString(), $node->class->getLine());
    }
}
