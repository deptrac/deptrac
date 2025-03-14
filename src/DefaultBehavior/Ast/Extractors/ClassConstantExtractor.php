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
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;

/**
 * @implements NikicReferenceExtractorInterface<ClassConstFetch>
 * @implements PHPStanReferenceExtractorInterface<ClassConstFetch>
 */
final class ClassConstantExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    public function getNodeType(): string
    {
        return ClassConstFetch::class;
    }

    /**
     * @param ClassConstFetch $node
     */
    private function processNodeShared(Node $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        if (!$node->class instanceof Name || $node->class->isSpecialClassName()) {
            return;
        }

        $referenceBuilder->dependency(
            ClassLikeToken::fromFQCN($node->class->toCodeString()),
            $node->class->getLine(),
            DependencyType::CONST,
        );
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        Scope $scope,
    ): void {
        $this->processNodeShared($node, $referenceBuilder);
    }
}
