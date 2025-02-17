<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
interface ParserInterface
{
    /**
     * @throws CouldNotParseFileException
     */
    public function parseFile(string $file) : FileReference;
}
