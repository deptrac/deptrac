<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;

/**
 * @implements NikicReferenceExtractorInterface<Interface_>
 * @implements PHPStanReferenceExtractorInterface<Interface_>
 */
final class InterfaceExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    /**
     * @param Interface_ $node
     */
    private function processNodeShared(Node $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        foreach ($node->extends as $extend) {
            $referenceBuilder->astInherits(ClassLikeToken::fromFQCN($extend->toCodeString()), $extend->getLine(), AstInheritType::IMPLEMENTS);
        }
    }

    public function getNodeType(): string
    {
        return Interface_::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        Scope $scope,
    ): void {
        $this->processNodeShared($node, $referenceBuilder);
    }
}
