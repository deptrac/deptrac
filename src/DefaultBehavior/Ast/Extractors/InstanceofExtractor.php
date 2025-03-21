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
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PHPStan\Analyser\MutatingScope;

/**
 * @implements NikicReferenceExtractorInterface<Instanceof_>
 * @implements PHPStanReferenceExtractorInterface<Instanceof_>
 */
final class InstanceofExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(private readonly TypeResolverInterface $typeResolver) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        if (!$node->class instanceof Name) {
            return;
        }

        foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->class) as $classLikeName) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($classLikeName), $node->class->getLine(), DependencyType::INSTANCEOF);
        }
    }

    public function getNodeType(): string
    {
        return Instanceof_::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        if (!$node->class instanceof Name) {
            return;
        }

        $referenceBuilder->dependency(ClassLikeToken::fromFQCN($scope->resolveName($node->class)), $node->class->getLine(), DependencyType::INSTANCEOF);
    }
}
