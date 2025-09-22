<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\PHPStanReferenceExtractorInterface;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\FileReferenceBuilder;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanContainerDecorator;
use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers\PhpStanFileReferenceVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPStan\DependencyInjection\MissingServiceException;
use PHPStan\Parser\ParserErrorsException;

class PhpStanParser extends AbstractParser
{
    /**
     * @param PHPStanReferenceExtractorInterface<Node>[] $extractors
     */
    public function __construct(
        private readonly PhpStanContainerDecorator $phpStanContainer,
        private readonly AstFileReferenceCacheInterface $cache,
        private readonly iterable $extractors,
    ) {
        $this->traverser = new NodeTraverser();
    }

    public function parseFile(string $file): FileReference
    {
        if (null !== $fileReference = $this->cache->get($file)) {
            return $fileReference;
        }

        try {
            $scopeFactory = $this->phpStanContainer->createScopeFactory();
            $reflectionProvider = $this->phpStanContainer->createReflectionProvider();
        } catch (MissingServiceException $exception) {
            throw CouldNotParseFileException::because('Could not initialize PHPStan.', $exception);
        }

        $fileReferenceBuilder = FileReferenceBuilder::create($file);
        $visitor = new PhpStanFileReferenceVisitor($fileReferenceBuilder, $scopeFactory, $reflectionProvider, $file, ...$this->extractors);
        $nodes = $this->loadNodesFromFile($file);
        $this->traverser->addVisitor($visitor);
        $this->traverser->traverse($nodes);
        $this->traverser->removeVisitor($visitor);

        return $fileReferenceBuilder->build();
    }

    protected function loadNodesFromFile(string $filepath): array
    {
        try {
            $parser = $this->phpStanContainer->createPHPStanParser();
            $nodes = $parser->parseFile($filepath);

            /** @var array<Node> $nodes */
            return $nodes;
        } catch (MissingServiceException $exception) {
            throw CouldNotParseFileException::because('Could not initialize PHPStan.', $exception);
        } catch (ParserErrorsException $exception) {
            throw CouldNotParseFileException::because($exception->getMessage(), $exception);
        }
    }
}
