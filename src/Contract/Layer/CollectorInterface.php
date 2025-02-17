<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Layer;

use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
/**
 * A collector is responsible to tell whether an AST node (e.g. a specific class) is part of a layer.
 */
interface CollectorInterface
{
    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidLayerDefinitionException
     * @throws InvalidCollectorDefinitionException
     * @throws CouldNotParseFileException
     */
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool;
}
