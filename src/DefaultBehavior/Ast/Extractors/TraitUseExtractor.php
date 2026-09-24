<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;

/**
 * @implements NikicReferenceExtractorInterface<Node\Stmt\TraitUse>
 * @implements PHPStanReferenceExtractorInterface<Node\Stmt\TraitUse>
 */
final readonly class TraitUseExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(private TypeResolverInterface $typeResolver) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, ...$node->traits) as $classLikeName) {
            $referenceBuilder->astInherits(ClassLikeToken::fromFQCN($classLikeName), $node->getLine(), AstInheritType::USES);
        }
    }

    public function getNodeType(): string
    {
        return Node\Stmt\TraitUse::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        foreach ($node->traits as $trait) {
            $referenceBuilder->astInherits(ClassLikeToken::fromFQCN($scope->resolveName($trait)), $node->getLine(), AstInheritType::USES);
        }
    }
}
