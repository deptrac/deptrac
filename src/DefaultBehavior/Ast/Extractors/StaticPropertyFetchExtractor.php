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
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;

/**
 * @implements NikicReferenceExtractorInterface<StaticPropertyFetch>
 * @implements PHPStanReferenceExtractorInterface<StaticPropertyFetch>
 */
final class StaticPropertyFetchExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(private readonly TypeResolverInterface $typeResolver) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        if (!$node->class instanceof Name) {
            return;
        }

        foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->class) as $classLikeName) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $node->class->getLine(), DependencyType::STATIC_PROPERTY);
        }
    }

    public function getNodeType(): string
    {
        return StaticPropertyFetch::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        Scope $scope
    ): void {
        if (!$node->class instanceof Name) {
            return;
        }

        $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($node->class)), $node->class->getLine(), DependencyType::STATIC_PROPERTY);
    }
}
