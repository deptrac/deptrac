<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\Extractors;

use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Name;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\Class_;
use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\Parser\TypeScope;
class AnonymousClassExtractor implements \Deptrac\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void
    {
        if (!$node instanceof Class_ || null !== $node->name) {
            return;
        }
        if ($node->extends instanceof Name) {
            $referenceBuilder->anonymousClassExtends($node->extends->toCodeString(), $node->extends->getLine());
        }
        foreach ($node->implements as $implement) {
            $referenceBuilder->anonymousClassImplements($implement->toCodeString(), $implement->getLine());
        }
        foreach ($node->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $referenceBuilder->anonymousClassTrait($trait->toCodeString(), $trait->getLine());
            }
        }
    }
}
