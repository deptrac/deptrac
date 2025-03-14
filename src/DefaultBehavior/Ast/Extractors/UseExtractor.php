<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\Scope;

/**
 * @implements NikicReferenceExtractorInterface<Use_>
 * @implements PHPStanReferenceExtractorInterface<Use_>
 */
final class UseExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    /**
     * @param Use_ $node
     */
    private function processNodeShared(Node $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        if (Use_::TYPE_NORMAL === $node->type) {
            foreach ($node->uses as $use) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($use->name->toString()), $use->name->getLine(), DependencyType::USE);
            }
        }
    }

    public function getNodeType(): string
    {
        return Use_::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        Scope $scope,
    ): void {
        $this->processNodeShared($node, $referenceBuilder);
    }
}
