<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;

/**
 * Interface for implementing custom Ast parsers. Useful if you need to extend
 * the functionality of the existing Nikic PHP parser or want to replace
 * the parser completely, for example with PHPStan parser.
 */
interface ParserInterface
{
    /**
     * @throws CouldNotParseFileException
     */
    public function parseFile(string $file): FileReference;

    /**
     * @return list<string> list of method names for a given class-like reference
     *
     * @throws CouldNotParseFileException
     */
    public function getMethodNamesForClassLikeReference(ClassLikeReference $classReference): array;
}
