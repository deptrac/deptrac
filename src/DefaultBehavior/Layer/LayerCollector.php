<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Contract\Layer\LayerResolverInterface;

use function array_key_exists;
use function is_string;
use function sprintf;

final class LayerCollector implements CollectorInterface
{
    /**
     * @var array<string, array<string, bool|null>>
     */
    private array $resolved = [];

    public function __construct(private readonly LayerResolverInterface $resolver) {}

    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!isset($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('LayerCollector: Missing configuration.');
        }
        if (!is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('LayerCollector: Configuration is not a string.');
        }

        $layer = $config['value'];

        if (!$this->resolver->has($layer)) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(sprintf('LayerCollector: Unknown layer "%s" specified in collector.', $layer));
        }
        $token = $reference->getToken()->toString();

        if (array_key_exists($token, $this->resolved) && array_key_exists($layer, $this->resolved[$token])) {
            if (null === $this->resolved[$token][$layer]) {
                throw InvalidLayerDefinitionException::circularTokenReference('LayerCollector', $token);
            }

            return $this->resolved[$token][$layer];
        }

        // Set resolved for current token to null in case resolver comes back to it (circular reference)
        $this->resolved[$token][$layer] = null;

        return $this->resolved[$token][$layer] = $this->resolver->isReferenceInLayer($config['value'], $reference);
    }
}
