<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser;

use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Core\Ast\AstMap\File\FileReference;
interface ParserInterface
{
    /**
     * @throws CouldNotParseFileException
     */
    public function parseFile(string $file) : FileReference;
}
