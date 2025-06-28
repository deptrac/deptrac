<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\VariableReference;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use JetBrains\PHPStormStub\PhpStormStubsMap;

final class PhpInternalCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if ($reference instanceof FileReference || $reference instanceof VariableReference) {
            return false;
        }

        if ($reference instanceof ClassLikeReference) {
            $token = $reference->getToken();

            return $token->match($this->getPattern($config)) && array_key_exists(
                $token->toString(), PhpStormStubsMap::CLASSES);
        }

        if ($reference instanceof FunctionReference) {
            $token = $reference->getToken();
            assert($token instanceof FunctionToken);

            return $token->match($this->getPattern($config)) && array_key_exists(
                $token->functionName, PhpStormStubsMap::FUNCTIONS);
        }

        // future-proof catch all
        return false;
    }

    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function getPattern(array $config): string
    {
        if (!isset($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('PhpInternalCollector: Missing configuration.');
        }
        if (!is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('PhpInternalCollector: Configuration is not a string.');
        }

        return '/'.$config['value'].'/i';
    }
}
