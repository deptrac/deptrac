<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer;

use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
interface LayerResolverInterface
{
    /**
     * @return array<string, bool> layer name and whether the dependency is public(true) or private(false)
     *
     * @throws InvalidLayerDefinitionException
     * @throws InvalidCollectorDefinitionException
     * @throws CouldNotParseFileException
     */
    public function getLayersForReference(TokenReferenceInterface $reference) : array;
    /**
     * @throws InvalidLayerDefinitionException
     * @throws InvalidCollectorDefinitionException
     * @throws CouldNotParseFileException
     */
    public function isReferenceInLayer(string $layer, TokenReferenceInterface $reference) : bool;
    /**
     * @throws InvalidLayerDefinitionException
     */
    public function has(string $layer) : bool;
}
