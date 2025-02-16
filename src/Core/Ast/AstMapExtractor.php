<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast;

use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\InputCollector\InputCollectorInterface;
use Deptrac\Deptrac\Core\InputCollector\InputException;
class AstMapExtractor
{
    private ?AstMap $astMapCache = null;
    public function __construct(private readonly InputCollectorInterface $inputCollector, private readonly \Deptrac\Deptrac\Core\Ast\AstLoader $astLoader)
    {
    }
    /**
     * @throws AstException
     */
    public function extract() : AstMap
    {
        try {
            return $this->astMapCache ??= $this->astLoader->createAstMap($this->inputCollector->collect());
        } catch (InputException $exception) {
            throw \Deptrac\Deptrac\Core\Ast\AstException::couldNotCollectFiles($exception);
        }
    }
}
