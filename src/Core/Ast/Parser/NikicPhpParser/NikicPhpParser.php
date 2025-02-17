<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser\NikicPhpParser;

use DEPTRAC_INTERNAL\PhpParser\Error;
use DEPTRAC_INTERNAL\PhpParser\ErrorHandler\Throwing;
use DEPTRAC_INTERNAL\PhpParser\Node;
use DEPTRAC_INTERNAL\PhpParser\Node\Identifier;
use DEPTRAC_INTERNAL\PhpParser\Node\Stmt\ClassLike;
use DEPTRAC_INTERNAL\PhpParser\NodeTraverser;
use DEPTRAC_INTERNAL\PhpParser\NodeVisitor\FindingVisitor;
use DEPTRAC_INTERNAL\PhpParser\NodeVisitor\NameResolver;
use DEPTRAC_INTERNAL\PhpParser\Parser;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReferenceBuilder;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\ReferenceExtractorInterface;
use Deptrac\Deptrac\Core\Ast\Parser\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Supportive\File\Exception\CouldNotReadFileException;
use Deptrac\Deptrac\Supportive\File\FileReader;
class NikicPhpParser implements ParserInterface
{
    /**
     * @var array<string, ClassLike>
     */
    private static array $classAstMap = [];
    private readonly NodeTraverser $traverser;
    /**
     * @param ReferenceExtractorInterface[] $extractors
     */
    public function __construct(private readonly Parser $parser, private readonly AstFileReferenceCacheInterface $cache, private readonly TypeResolver $typeResolver, private readonly iterable $extractors)
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
    }
    public function parseFile(string $file) : FileReference
    {
        if (null !== ($fileReference = $this->cache->get($file))) {
            return $fileReference;
        }
        $fileReferenceBuilder = FileReferenceBuilder::create($file);
        $visitor = new \Deptrac\Deptrac\Core\Ast\Parser\NikicPhpParser\FileReferenceVisitor($fileReferenceBuilder, $this->typeResolver, ...$this->extractors);
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
    public function getNodeForClassLikeReference(ClassLikeReference $classReference) : ?ClassLike
    {
        $classLikeName = $classReference->getToken()->toString();
        if (isset(self::$classAstMap[$classLikeName])) {
            return self::$classAstMap[$classLikeName];
        }
        $filepath = $classReference->getFilepath();
        if (null === $filepath) {
            return null;
        }
        $visitor = new FindingVisitor(static fn(Node $node): bool => $node instanceof ClassLike);
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
            self::$classAstMap[$className] = $classLikeNode;
        }
        /** @psalm-var ?ClassLike */
        return self::$classAstMap[$classLikeName] ?? null;
    }
    /**
     * @return array<Node>
     *
     * @throws CouldNotParseFileException
     */
    private function loadNodesFromFile(string $filepath) : array
    {
        try {
            $fileContents = FileReader::read($filepath);
            /** @throws Error */
            $nodes = $this->parser->parse($fileContents, new Throwing());
            /** @var array<Node> $nodes */
            return $nodes;
        } catch (Error|CouldNotReadFileException $e) {
            throw CouldNotParseFileException::because($e->getMessage(), $e);
        }
    }
}
