<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use DEPTRAC_INTERNAL\JetBrains\PHPStormStub\PhpStormStubsMap;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionToken;
use Deptrac\Deptrac\Core\Ast\AstMap\Variable\VariableReference;
class PhpInternalCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if ($reference instanceof FileReference || $reference instanceof VariableReference) {
            return \false;
        }
        if ($reference instanceof ClassLikeReference) {
            $token = $reference->getToken();
            return $token->match($this->getPattern($config)) && \array_key_exists($token->toString(), PhpStormStubsMap::CLASSES);
        }
        if ($reference instanceof FunctionReference) {
            $token = $reference->getToken();
            \assert($token instanceof FunctionToken);
            return $token->match($this->getPattern($config)) && \array_key_exists($token->functionName, PhpStormStubsMap::FUNCTIONS);
        }
        // future-proof catch all
        return \false;
    }
    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function getPattern(array $config) : string
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('PhpInternalCollector needs configuration.');
        }
        return '/' . $config['value'] . '/i';
    }
}
