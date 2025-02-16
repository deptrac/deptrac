<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
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
