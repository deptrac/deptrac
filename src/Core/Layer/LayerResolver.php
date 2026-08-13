<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Config\CollectorScope;
use Deptrac\Deptrac\Contract\Layer\Collectable;
use Deptrac\Deptrac\Contract\Layer\CollectorResolverInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Contract\Layer\LayerResolverInterface;

use function array_key_exists;

class LayerResolver implements LayerResolverInterface
{
    /**
     * @var array<string, Collectable[]>
     */
    private array $layers = [];

    private bool $initialized = false;

    /**
     * @var array<string, array<string, bool>>
     */
    private array $resolved = [];

    /**
     * @param array<array{name?: string, collectors?: array<array<string, string|array<string, string>>>}> $layersConfig
     */
    public function __construct(
        private readonly CollectorResolverInterface $collectorResolver,
        private readonly array $layersConfig,
    ) {}

    public function getLayersForReference(TokenReferenceInterface $reference): array
    {
        if (false === $this->initialized) {
            $this->initializeLayers();
        }

        $tokenName = $reference->getToken()->toString();
        if (array_key_exists($tokenName, $this->resolved)) {
            return $this->resolved[$tokenName];
        }

        $this->resolved[$tokenName] = [];

        foreach ($this->layers as $layer => $collectables) {
            foreach ($collectables as $collectable) {
                if (!$this->scopeAccepts($collectable, $reference)) {
                    continue;
                }

                $attributes = $collectable->attributes;

                if ($collectable->collector->satisfy($attributes, $reference)) {
                    if (array_key_exists($layer, $this->resolved[$tokenName]) && $this->resolved[$tokenName][$layer]) {
                        continue;
                    }
                    if (array_key_exists('private', $attributes) && true === $attributes['private']) {
                        $this->resolved[$tokenName][$layer] = false;
                    } else {
                        $this->resolved[$tokenName][$layer] = true;
                    }
                }
            }
        }

        return $this->resolved[$tokenName];
    }

    public function isReferenceInLayer(string $layer, TokenReferenceInterface $reference): bool
    {
        if (false === $this->initialized) {
            $this->initializeLayers();
        }

        $tokenName = $reference->getToken()->toString();
        if (array_key_exists($tokenName, $this->resolved) && [] !== $this->resolved[$tokenName]) {
            return array_key_exists($layer, $this->resolved[$tokenName]);
        }

        if (!array_key_exists($layer, $this->layers)) {
            return false;
        }

        $collectables = $this->layers[$layer];

        foreach ($collectables as $collectable) {
            if (!$this->scopeAccepts($collectable, $reference)) {
                continue;
            }

            if ($collectable->collector->satisfy($collectable->attributes, $reference)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Method references are only matched by collectors declared with
     * "scope: method"; every other reference is only matched by collectors
     * without it (or with the default "scope: class").
     */
    private function scopeAccepts(Collectable $collectable, TokenReferenceInterface $reference): bool
    {
        $scope = $collectable->attributes['scope'] ?? CollectorScope::TYPE_CLASS->value;

        return (CollectorScope::TYPE_METHOD->value === $scope) === $reference instanceof ClassMethodReference;
    }

    public function has(string $layer): bool
    {
        if (false === $this->initialized) {
            $this->initializeLayers();
        }

        return array_key_exists($layer, $this->layers);
    }

    /**
     * @throws InvalidLayerDefinitionException
     * @throws InvalidCollectorDefinitionException
     */
    private function initializeLayers(): void
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
                $collectable = $this->collectorResolver->resolve($config);
                $this->assertValidScope($collectable);
                $this->layers[$layerName][] = $collectable;
            }
            if ([] === $this->layers[$layerName]) {
                throw InvalidLayerDefinitionException::collectorRequired($layerName);
            }
        }

        if ([] === $this->layers) {
            throw InvalidLayerDefinitionException::layerRequired();
        }

        $this->initialized = true;
    }

    /**
     * @throws InvalidCollectorDefinitionException
     */
    private function assertValidScope(Collectable $collectable): void
    {
        if (!array_key_exists('scope', $collectable->attributes)) {
            return;
        }

        $scope = $collectable->attributes['scope'];

        if (!is_string($scope) || null === CollectorScope::tryFrom($scope)) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(sprintf('Unknown collector scope "%s". Available scopes: %s.', is_string($scope) ? $scope : get_debug_type($scope), implode(', ', CollectorScope::values())));
        }
    }
}
