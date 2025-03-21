<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;

abstract class AbstractParser implements ParserInterface
{
    /**
     * @var array<string, list<string>>
     */
    private static array $classAstMap = [];

    protected NodeTraverser $traverser;

    /**
     * @throws CouldNotParseFileException
     */
    public function getMethodNamesForClassLikeReference(ClassLikeReference $classReference): array
    {
        $classLikeName = $classReference->getToken()->toString();

        if (isset(self::$classAstMap[$classLikeName])) {
            return self::$classAstMap[$classLikeName];
        }

        $filepath = $classReference->getFilepath();

        if (null === $filepath) {
            return [];
        }

        $visitor = new FindingVisitor(static fn (Node $node): bool => $node instanceof ClassLike);
        $nodes = $this->loadNodesFromFile($filepath);
        $this->traverser->addVisitor($visitor);
        $this->traverser->traverse($nodes);
        $this->traverser->removeVisitor($visitor);

        /** @var ClassLike[] $classLikeNodes */
        $classLikeNodes = $visitor->getFoundNodes();

        foreach ($classLikeNodes as $classLikeNode) {
            if (isset($classLikeNode->namespacedName)) {
                $namespacedName = $classLikeNode->namespacedName;
                $className = $namespacedName->toCodeString();
            } elseif ($classLikeNode->name instanceof Identifier) {
                $className = $classLikeNode->name->toString();
            } else {
                continue;
            }

            self::$classAstMap[$className] = array_map(
                static fn (Node\Stmt\ClassMethod $method): string => (string) $method->name,
                $classLikeNode->getMethods()
            );
        }

        /** @psalm-var list<string> */
        return self::$classAstMap[$classLikeName] ?? [];
    }

    /**
     * @return array<Node>
     *
     * @throws CouldNotParseFileException
     */
    abstract protected function loadNodesFromFile(string $filepath): array;
}
