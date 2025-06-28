<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\Contract\Ast\ReferenceExtractorInterface;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceVisitor;
use PhpParser\Error;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\FindingVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use RuntimeException;

class NikicPhpParser implements ParserInterface
{
    /**
     * @var array<string, list<string>>
     */
    private static array $classAstMap = [];

    private readonly NodeTraverser $traverser;

    /**
     * @param ReferenceExtractorInterface<Node>[] $extractors
     */
    public function __construct(
        private readonly Parser $parser,
        private readonly AstFileReferenceCacheInterface $cache,
        private readonly iterable $extractors,
    ) {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
    }

    public function parseFile(string $file): FileReference
    {
        if (null !== $fileReference = $this->cache->get($file)) {
            return $fileReference;
        }

        $fileReferenceBuilder = FileReferenceBuilder::create($file);
        $visitor = new FileReferenceVisitor($fileReferenceBuilder, ...$this->extractors);
        $nodes = $this->loadNodesFromFile($file);
        $this->traverser->addVisitor($visitor);
        $this->traverser->traverse($nodes);
        $this->traverser->removeVisitor($visitor);

        $fileReference = $fileReferenceBuilder->build();
        $this->cache->set($fileReference);

        return $fileReference;
    }

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
                array_values($classLikeNode->getMethods())
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
    protected function loadNodesFromFile(string $filepath): array
    {
        try {
            $fileContents = @file_get_contents($filepath);

            if (false === $fileContents) {
                throw new RuntimeException(sprintf('File "%s" cannot be read.', $filepath));
            }

            /** @throws Error */
            $nodes = $this->parser->parse($fileContents, new Throwing());

            /** @var array<Node> $nodes */
            return $nodes;
        } catch (Error|RuntimeException $e) {
            throw CouldNotParseFileException::because($e->getMessage(), $e);
        }
    }
}
