<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\Extractors;

use Deptrac\Deptrac\Core\Ast\AstMap\ReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\AstMap\Variable\SuperGlobalToken;
use Deptrac\Deptrac\Core\Ast\Parser\TypeScope;
use DEPTRAC_INTERNAL\PhpParser\Node;
class VariableExtractor implements \Deptrac\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface
{
    /**
     * @var list<string>
     */
    private array $allowedNames;
    public function __construct()
    {
        $this->allowedNames = SuperGlobalToken::allowedNames();
    }
    public function processNode(Node $node, ReferenceBuilder $referenceBuilder, TypeScope $typeScope) : void
    {
        if ($node instanceof Node\Expr\Variable && \in_array($node->name, $this->allowedNames, \true)) {
            $referenceBuilder->superglobal($node->name, $node->getLine());
        }
    }
}
