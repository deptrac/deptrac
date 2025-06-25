<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeAbstract;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;

/**
 * Helper interface in resolving Nikic PHP parser (and PHPStan Doc parser) nodes
 * into FQCN to be used inside the reference extractor implementation
 * for defining references between tokens.
 *
 * @see ReferenceExtractorInterface
 */
interface TypeResolverInterface
{
    /**
     * Resolves Nikic PHPParser nodes to their FQCN (Full Qualified Class Name) given the current scope.
     *
     * @return string[]
     */
    public function resolvePHPParserTypes(TypeScope $typeScope, NodeAbstract ...$nodes): array;

    /**
     * Resolves Doc comment nodes parsed by PHPStan Doc Parser to their FQCN (Full Qualified Class Name) given the current scope.
     *
     * @param array<string> $templateTypes
     *
     * @return string[]
     */
    public function resolvePHPStanDocParserType(TypeNode $type, TypeScope $typeScope, array $templateTypes): array;

    /**
     * Resolves Nikic PHPParser property nodes to their FQCN (Full Qualified Class Name) given the current scope.
     *
     * @return string[]
     */
    public function resolvePropertyType(Identifier|Name|ComplexType $type): array;
}
