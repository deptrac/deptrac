<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use PhpParser\Node;
use PHPStan\Analyser\Scope;

/**
 * @template T of Node
 */
interface PHPStanReferenceExtractorInterface
{
    /**
     * @return class-string<T>
     */
    public function getNodeType(): string;

    /**
     * @param T $node
     */
    public function processNodeWithPhpStanScope(Node $node, ReferenceBuilderInterface $referenceBuilder, Scope $scope): void;
}
