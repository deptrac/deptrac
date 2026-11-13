<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionCallDependencyEmitter;
use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Analyser\MutatingScope;

/**
 * Unqualified function names inside a namespace cannot be statically
 * resolved. Inside a namespace Foo, a call to strlen() may either refer to
 * the namespaced \Foo\strlen(), or the global \strlen(). Which one it is
 * depends on whether \Foo\strlen() is declared, and that is not known while
 * a single file is parsed.
 *
 * For such a call, both candidates are recorded: first the namespaced
 * function, then the global function PHP falls back to. Deciding between the
 * two is left to the {@see FunctionCallDependencyEmitter}.
 *
 * @implements NikicReferenceExtractorInterface<Node\Expr\FuncCall>
 * @implements PHPStanReferenceExtractorInterface<Node\Expr\FuncCall>
 */
final class FunctionCallExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        $this->recordCandidates($node, $referenceBuilder);
    }

    public function getNodeType(): string
    {
        return Node\Expr\FuncCall::class;
    }

    public function processNodeWithPhpStanScope(
        Node $node,
        ReferenceBuilderInterface $referenceBuilder,
        MutatingScope $scope,
    ): void {
        $this->recordCandidates($node, $referenceBuilder);
    }

    private function recordCandidates(Node\Expr\FuncCall $node, ReferenceBuilderInterface $referenceBuilder): void
    {
        foreach ($this->candidates($node->name) as $functionName) {
            $referenceBuilder->dependency(FunctionToken::fromFQCN($functionName), $node->getLine(), DependencyType::UNRESOLVED_FUNCTION_CALL);
        }
    }

    /**
     * @return list<string>
     */
    private function candidates(Node\Expr|Name $name): array
    {
        // The called function is only known at run-time, as in $function().
        if (!$name instanceof Name) {
            return [];
        }

        // The name resolver leaves an unqualified name untouched if it cannot
        // tell which function is meant, and attaches the namespaced name. Any
        // other name is fully qualified by now.
        $namespacedName = $name->getAttribute('namespacedName');

        if ($namespacedName instanceof Name) {
            return [$namespacedName->toString(), $name->toString()];
        }

        return [$name->toString()];
    }
}
