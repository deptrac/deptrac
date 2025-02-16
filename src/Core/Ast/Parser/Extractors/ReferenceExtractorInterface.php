<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\Extractors;

use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\Parser\TypeScope;
use DEPTRAC_INTERNAL\PhpParser\Node;
interface ReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void;
}
