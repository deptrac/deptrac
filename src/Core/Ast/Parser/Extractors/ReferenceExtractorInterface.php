<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser\Extractors;

use DEPTRAC_INTERNAL\PhpParser\Node;
use Qossmic\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Qossmic\Deptrac\Core\Ast\Parser\TypeScope;
interface ReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void;
}
