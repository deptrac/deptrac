<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;

final class FunctionNameCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!$reference instanceof FunctionReference) {
            return false;
        }

        /** @var FunctionToken $tokenName */
        $tokenName = $reference->getToken();

        return $tokenName->match($this->getPattern($config));
    }

    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function getPattern(array $config): string
    {
        if (!isset($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('FunctionNameCollector: Missing configuration.');
        }
        if (!is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('FunctionNameCollector: Configuration is not a string.');
        }

        return '/'.$config['value'].'/i';
    }
}
