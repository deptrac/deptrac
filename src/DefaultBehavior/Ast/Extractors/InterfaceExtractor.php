<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\ReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;

/**
 * @implements ReferenceExtractorInterface<Interface_>
 */
final class InterfaceExtractor implements ReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($node->extends as $extend) {
            $referenceBuilder->astInherits(ClassLikeToken::fromFQCN($extend->toCodeString()), $extend->getLine(), AstInheritType::IMPLEMENTS);
        }
    }

    public function getNodeType(): string
    {
        return Interface_::class;
    }
}
