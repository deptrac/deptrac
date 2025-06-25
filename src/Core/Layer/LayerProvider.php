<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Layer;

use Deptrac\Deptrac\Contract\Layer\CircularReferenceException;
use Deptrac\Deptrac\Contract\Layer\LayerProviderInterface;

/**
 * Provides information about layer configuration.
 */
final class LayerProvider implements LayerProviderInterface
{
    /**
     * @param array<string, list<string>> $allowedLayers source layer -> target layers
     */
    public function __construct(private readonly array $allowedLayers) {}

    /**
     * @return list<string>
     *
     * @throws CircularReferenceException
     */
    public function getAllowedLayers(string $layerName): array
    {
        return array_values(array_unique($this->getTransitiveDependencies($layerName, [])));
    }

    /**
     * @param list<string> $previousLayers
     *
     * @return string[]
     *
     * @throws CircularReferenceException
     */
    private function getTransitiveDependencies(string $layerName, array $previousLayers): array
    {
        if (in_array($layerName, $previousLayers, true)) {
            throw CircularReferenceException::circularLayerDependency($layerName, $previousLayers);
        }
        $dependencies = [];
        foreach ($this->allowedLayers[$layerName] ?? [] as $layer) {
            if (str_starts_with($layer, '+')) {
                $layer = ltrim($layer, '+');
                $dependencies[] = $this->getTransitiveDependencies($layer, array_merge([$layerName], $previousLayers));
            }
            $dependencies[] = [$layer];
        }

        return [] === $dependencies ? [] : array_merge(...$dependencies);
    }
}
