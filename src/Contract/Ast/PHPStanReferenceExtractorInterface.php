<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;

/**
 * @template T of Node
 *
 * @extends BaseReferenceExtractorInterface<T>
 */
interface PHPStanReferenceExtractorInterface extends BaseReferenceExtractorInterface
{
    /**
     * @param T $node
     */
    public function processNodeWithPhpStanScope(Node $node, ReferenceBuilderInterface $referenceBuilder, MutatingScope $scope): void;
}
