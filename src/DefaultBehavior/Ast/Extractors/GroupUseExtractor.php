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
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Analyser\MutatingScope;

/**
 * @implements NikicReferenceExtractorInterface<GroupUse>
 * @implements PHPStanReferenceExtractorInterface<GroupUse>
 */
final class GroupUseExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    /**
     * @param GroupUse $node
     */
    private function processNodeShared(Node $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        foreach ($node->uses as $use) {
            if (Use_::TYPE_NORMAL === $use->type) {
                $classLikeName = $node->prefix->toString().'\\'.$use->name->toString();
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $use->name->getLine(), DependencyType::USE);
            }
        }
    }

    public function getNodeType(): string
    {
        return GroupUse::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        $this->processNodeShared($node, $referenceBuilder);
    }
}
