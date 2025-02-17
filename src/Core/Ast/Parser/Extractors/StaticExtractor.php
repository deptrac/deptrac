<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser\Extractors;

use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\StaticCall;
use DEPTRAC_INTERNAL\PhpParser\Node\Expr\StaticPropertyFetch;
use DEPTRAC_INTERNAL\PhpParser\Node\Name;
use Qossmic\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Qossmic\Deptrac\Core\Ast\Parser\TypeResolver;
use Qossmic\Deptrac\Core\Ast\Parser\TypeScope;
class StaticExtractor implements \Qossmic\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
{
    public function __construct(private readonly TypeResolver $typeResolver)
    {
    }
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void
    {
        if ($node instanceof StaticPropertyFetch && $node->class instanceof Name) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->class) as $classLikeName) {
                $referenceBuilder->staticProperty($classLikeName, $node->class->getLine());
            }
        }
        if ($node instanceof StaticCall && $node->class instanceof Name) {
            foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->class) as $classLikeName) {
                $referenceBuilder->staticMethod($classLikeName, $node->class->getLine());
            }
        }
    }
}
