<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Core\Layer\Collector\Collectable;
use Deptrac\Deptrac\Core\Layer\Collector\CollectorResolverInterface;
use function array_key_exists;
class LayerResolver implements \Deptrac\Deptrac\Core\Layer\LayerResolverInterface
{
    /**
     * @var array<string, Collectable[]>
     */
    private array $layers = [];
    private bool $initialized = \false;
    /**
     * @var array<string, array<string, bool>>
     */
    private array $resolved = [];
    /**
     * @param array<array{name?: string, collectors?: array<array<string, string|array<string, string>>>}> $layersConfig
     */
    public function __construct(private readonly CollectorResolverInterface $collectorResolver, private readonly array $layersConfig)
    {
    }
    public function getLayersForReference(TokenReferenceInterface $reference) : array
    {
        if (\false === $this->initialized) {
            $this->initializeLayers();
        }
        $tokenName = $reference->getToken()->toString();
        if (array_key_exists($tokenName, $this->resolved)) {
            return $this->resolved[$tokenName];
        }
        $this->resolved[$tokenName] = [];
        foreach ($this->layers as $layer => $collectables) {
            foreach ($collectables as $collectable) {
                $attributes = $collectable->attributes;
                if ($collectable->collector->satisfy($attributes, $reference)) {
                    if (array_key_exists($layer, $this->resolved[$tokenName]) && $this->resolved[$tokenName][$layer]) {
                        continue;
                    }
                    if (array_key_exists('private', $attributes) && \true === $attributes['private']) {
                        $this->resolved[$tokenName][$layer] = \false;
                    } else {
                        $this->resolved[$tokenName][$layer] = \true;
                    }
                }
            }
        }
        return $this->resolved[$tokenName];
    }
    public function isReferenceInLayer(string $layer, TokenReferenceInterface $reference) : bool
    {
        if (\false === $this->initialized) {
            $this->initializeLayers();
        }
        $tokenName = $reference->getToken()->toString();
        if (array_key_exists($tokenName, $this->resolved) && [] !== $this->resolved[$tokenName]) {
            return array_key_exists($layer, $this->resolved[$tokenName]);
        }
        if (!array_key_exists($layer, $this->layers)) {
            return \false;
        }
        $collectables = $this->layers[$layer];
        foreach ($collectables as $collectable) {
            if ($collectable->collector->satisfy($collectable->attributes, $reference)) {
                return \true;
            }
        }
        return \false;
    }
    public function has(string $layer) : bool
    {
        if (\false === $this->initialized) {
            $this->initializeLayers();
        }
        return array_key_exists($layer, $this->layers);
    }
    /**
     * @throws InvalidLayerDefinitionException
     */
    private function initializeLayers() : void
    {
        $this->layers = [];
        foreach ($this->layersConfig as $layer) {
            if (!array_key_exists('name', $layer)) {
                throw InvalidLayerDefinitionException::missingName();
            }
            $layerName = $layer['name'];
            if (array_key_exists($layerName, $this->layers)) {
                throw InvalidLayerDefinitionException::duplicateName($layerName);
            }
            $this->layers[$layerName] = [];
            foreach ($layer['collectors'] ?? [] as $config) {
                $this->layers[$layerName][] = $this->collectorResolver->resolve($config);
            }
            if ([] === $this->layers[$layerName]) {
                throw InvalidLayerDefinitionException::collectorRequired($layerName);
            }
        }
        if ([] === $this->layers) {
            throw InvalidLayerDefinitionException::layerRequired();
        }
        $this->initialized = \true;
    }
}
