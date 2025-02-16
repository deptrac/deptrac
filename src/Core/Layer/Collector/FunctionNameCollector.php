<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionToken;
final class FunctionNameCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof FunctionReference) {
            return \false;
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
    private function getPattern(array $config) : string
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('FunctionNameCollector needs the regex configuration.');
        }
        return '/' . $config['value'] . '/i';
    }
}
