<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;

final class SuperglobalCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!$reference instanceof VariableReference) {
            return false;
        }

        return in_array($reference->getToken()->toString(), $this->getNames($config), true);
    }

    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @return string[]
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function getNames(array $config): array
    {
        if (!isset($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('SuperglobalCollector: Missing configuration.');
        }
        if (!is_array($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('SuperglobalCollector: Configuration is not an array.');
        }

        return array_map(static fn ($name): string => '$'.$name, $config['value']);
    }
}
