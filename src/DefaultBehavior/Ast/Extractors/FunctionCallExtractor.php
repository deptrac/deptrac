<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Extractors;

use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ReferenceBuilderInterface;
use Deptrac\Deptrac\Contract\Ast\ReferenceExtractorInterface;
use Deptrac\Deptrac\Contract\Ast\TypeResolverInterface;
use Deptrac\Deptrac\Contract\Ast\TypeScope;
use PhpParser\Node;

/**
 * Unqualified function and constant names inside a namespace cannot be
 * statically resolved. Inside a namespace Foo, a call to strlen() may
 * either refer to the namespaced \Foo\strlen(), or the global \strlen().
 * Because PHP-Parser does not have the necessary context to decide this,
 * such names are left unresolved.
 *
 * @implements ReferenceExtractorInterface<\PhpParser\Node\Expr\FuncCall>
 */
final class FunctionCallExtractor implements ReferenceExtractorInterface
{
    public function __construct(
        private readonly TypeResolverInterface $typeResolver,
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
}
