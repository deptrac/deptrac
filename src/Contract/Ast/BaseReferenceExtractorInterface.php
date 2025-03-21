<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use PhpParser\Node;

/**
 * @template T of Node
 */
interface BaseReferenceExtractorInterface
{
    /**
     * @return class-string<T>
     */
    public function getNodeType(): string;
}
