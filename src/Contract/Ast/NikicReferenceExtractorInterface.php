<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use PhpParser\Node;

/**
 * Interface for defining references between tokens. You can catch any Nikic PHP
 * parser node and define a dependency based on this node on another token.
 *
 * @template T of Node
 *
 * @extends BaseReferenceExtractorInterface<T>
 */
interface NikicReferenceExtractorInterface extends BaseReferenceExtractorInterface
{
    /**
     * @param T $node
     */
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void;
}
