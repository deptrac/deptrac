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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\MutatingScope;

/**
 * @implements NikicReferenceExtractorInterface<Class_>
 * @implements PHPStanReferenceExtractorInterface<Class_>
 */
final class AnonymousClassExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    /**
     * @param Class_ $node
     */
    private function processNodeShared(Node $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        if (null !== $node->name) {
            return;
        }

        if ($node->extends instanceof Name) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($node->extends->toCodeString()), $node->extends->getLine(), DependencyType::ANONYMOUS_CLASS_EXTENDS);
        }

        foreach ($node->implements as $implement) {
            $referenceBuilder->dependency(ClassLikeToken::fromFQCN($implement->toCodeString()), $implement->getLine(), DependencyType::ANONYMOUS_CLASS_IMPLEMENTS);
        }

        foreach ($node->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $referenceBuilder->dependency(ClassLikeToken::fromFQCN($trait->toCodeString()), $trait->getLine(), DependencyType::ANONYMOUS_CLASS_TRAIT);
            }
        }
    }

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    public function processNodeWithPhpStanScope(Node $node, ReferenceBuilderInterface $referenceBuilder, MutatingScope $scope): void
    {
        $this->processNodeShared($node, $referenceBuilder);
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }
}
