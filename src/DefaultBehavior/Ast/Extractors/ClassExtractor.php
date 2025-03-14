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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;

/**
 * @implements NikicReferenceExtractorInterface<Class_>
 * @implements PHPStanReferenceExtractorInterface<Class_>
 */
final class ClassExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        Scope $scope,
    ): void {
        $this->processNodeShared($node, $referenceBuilder);
    }

    /**
     * @param Class_ $node
     */
    private function processNodeShared(Node $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        if (null !== $node->name) {
            if ($node->extends instanceof Name) {
                $referenceBuilder->astInherits(ClassLikeToken::fromFQCN($node->extends->toCodeString()), $node->extends->getLine(), AstInheritType::EXTENDS);
            }

            foreach ($node->implements as $implement) {
                $referenceBuilder->astInherits(ClassLikeToken::fromFQCN($implement->toCodeString()), $implement->getLine(), AstInheritType::IMPLEMENTS);
            }
        }
    }
}
