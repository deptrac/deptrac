<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\NikicReferenceExtractorInterface;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\NikicFileReferenceVisitor;
use PhpParser\Error;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use RuntimeException;

class NikicPhpParser extends AbstractParser
{
    /**
     * @param NikicReferenceExtractorInterface<Node>[] $extractors
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
        $visitor = new NikicFileReferenceVisitor($fileReferenceBuilder, ...$this->extractors);
        $nodes = $this->loadNodesFromFile($file);
        $this->traverser->addVisitor($visitor);
        $this->traverser->traverse($nodes);
        $this->traverser->removeVisitor($visitor);

        $fileReference = $fileReferenceBuilder->build();
        $this->cache->set($fileReference);

        return $fileReference;
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
