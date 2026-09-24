<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PHPStan\Analyser\MutatingScope;

/**
 * @implements NikicReferenceExtractorInterface<Catch_>
 * @implements PHPStanReferenceExtractorInterface<Catch_>
 */
final readonly class CatchExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(private TypeResolverInterface $typeResolver) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, ...$node->types) as $classLikeName) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $node->getLine(), DependencyType::CATCH);
        }
    }

    public function getNodeType(): string
    {
        return Catch_::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        foreach ($node->types as $classLikeName) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($classLikeName)), $node->getLine(), DependencyType::CATCH);
        }
    }
}
