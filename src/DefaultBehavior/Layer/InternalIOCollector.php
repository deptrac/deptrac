<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\FunctionToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\Helpers\InternalIOMap;

use function is_string;
use function preg_match;
use function strtolower;

/**
 * Collects the PHP-internal functions and classes that perform I/O.
 *
 * Nobody maintains a list of these. Instead, a function is considered to
 * perform I/O if a `resource`, or a handle class that replaced a resource
 * such as `CurlHandle`, is part of its signature. A class is collected if it
 * is such a handle class or if this holds for one of its methods.
 *
 * @see InternalIOMap
 */
final class InternalIOCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        $name = $this->getInternalIOName($reference);

        if (null === $name) {
            return false;
        }

        $exclude = $this->getExcludePattern($config);

        return null === $exclude || 1 !== preg_match($exclude, $name);
    }

    private function getInternalIOName(TokenReferenceInterface $reference): ?string
    {
        if ($reference instanceof FunctionReference) {
            $token = $reference->getToken();
            assert($token instanceof FunctionToken);

            return isset(InternalIOMap::FUNCTIONS[strtolower($token->functionName)]) ? $token->functionName : null;
        }

        if ($reference instanceof ClassLikeReference) {
            $name = $reference->getToken()->toString();

            return isset(InternalIOMap::CLASSES[strtolower($name)]) ? $name : null;
        }

        return null;
    }

    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function getExcludePattern(array $config): ?string
    {
        if (!isset($config['exclude'])) {
            return null;
        }

        if (!is_string($config['exclude'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('InternalIOCollector: "exclude" is not a string.');
        }

        if ('' === $config['exclude']) {
            return null;
        }

        $pattern = '/'.$config['exclude'].'/i';

        if (false === @preg_match($pattern, '')) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('InternalIOCollector: Invalid regex pattern '.$pattern);
        }

        return $pattern;
    }
}
