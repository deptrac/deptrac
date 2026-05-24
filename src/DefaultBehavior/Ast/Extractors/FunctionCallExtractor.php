<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;
use PHPStan\Analyser\MutatingScope;

/**
 * Unqualified function and constant names inside a namespace cannot be
 * statically resolved. Inside a namespace Foo, a call to strlen() may
 * either refer to the namespaced \Foo\strlen(), or the global \strlen().
 * Because PHP-Parser does not have the necessary context to decide this,
 * such names are left unresolved.
 *
 * @implements NikicReferenceExtractorInterface<Node\Expr\FuncCall>
 * @implements PHPStanReferenceExtractorInterface<Node\Expr\FuncCall>
 */
final readonly class FunctionCallExtractor implements NikicReferenceExtractorInterface, PHPStanReferenceExtractorInterface
{
    public function __construct(
        private TypeResolverInterface $typeResolver,
    ) {}

    public function processNode(Node $node, ReferenceBuilderInterface $referenceBuilder, TypeScope $typeScope): void
    {
        foreach ($this->typeResolver->resolvePHPParserTypes($typeScope, $node->name) as $functionName) {
            $referenceBuilder->dependency(FunctionToken::fromFQCN($functionName), $node->getLine(), DependencyType::UNRESOLVED_FUNCTION_CALL);
        }
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
        foreach ($this->typeResolver->resolveType($node->name, $scope) as $functionName) {
            $referenceBuilder->dependency(FunctionToken::fromFQCN($functionName), $node->getLine(), DependencyType::UNRESOLVED_FUNCTION_CALL);
        }
    }
}
