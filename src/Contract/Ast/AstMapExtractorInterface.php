<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstMapInterface;

interface AstMapExtractorInterface
{
    /**
     * @throws AstException
     */
    public function extract(): AstMapInterface;
}
