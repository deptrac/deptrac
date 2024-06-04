<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast;

use DEPTRAC_INTERNAL\Psr\EventDispatcher\EventDispatcherInterface;
use Qossmic\Deptrac\Contract\Ast\AstFileAnalysedEvent;
use Qossmic\Deptrac\Contract\Ast\AstFileSyntaxErrorEvent;
use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Contract\Ast\PostCreateAstMapEvent;
use Qossmic\Deptrac\Contract\Ast\PreCreateAstMapEvent;
use Qossmic\Deptrac\Core\Ast\AstMap\AstMap;
use Qossmic\Deptrac\Core\Ast\Parser\ParserInterface;
class AstLoader
{
    public function __construct(private readonly ParserInterface $parser, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }
    /**
     * @param list<string> $files
     */
    public function createAstMap(array $files) : AstMap
    {
        $references = [];
        $this->eventDispatcher->dispatch(new PreCreateAstMapEvent(\count($files)));
        foreach ($files as $file) {
            try {
                $references[] = $this->parser->parseFile($file);
                $this->eventDispatcher->dispatch(new AstFileAnalysedEvent($file));
            } catch (CouldNotParseFileException $e) {
                $this->eventDispatcher->dispatch(new AstFileSyntaxErrorEvent($file, $e->getMessage()));
            }
        }
        $astMap = new AstMap($references);
        $this->eventDispatcher->dispatch(new PostCreateAstMapEvent());
        return $astMap;
    }
}
