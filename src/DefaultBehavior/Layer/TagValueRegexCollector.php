<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\TaggedTokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;

final class TagValueRegexCollector implements CollectorInterface
{
    /**
     * @param array<string, bool|string|array<string, string>> $config
     */
    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!$reference instanceof TaggedTokenReferenceInterface) {
            return false;
        }

        $tagLines = $reference->getTagLines($this->getTagName($config));
        $pattern = $this->getValidatedPattern($config);

        if (null === $tagLines || [] === $tagLines) {
            return false;
        }

        foreach ($tagLines as $line) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, bool|string|array<string, string>|null> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    protected function getTagName(array $config): string
    {
        if (!isset($config['tag'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector: Missing "tag" configuration.');
        }
        if (!is_string($config['tag'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector: Configuration "tag" is not a string.');
        }

        if (!preg_match('/^@[-\w]+$/', $config['tag'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector: Invalid "tag" name.');
        }

        return $config['tag'];
    }

    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    protected function getValidatedPattern(array $config): string
    {
        if (!isset($config['value'])) {
            return '/^.?/'; // any string
        }

        $pattern = $config['value'];

        if (!is_string($pattern)) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector: "value" configuration is not a string.');
        }

        if ('' === $pattern) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector: "value" configuration is empty string.');
        }

        if (false === @preg_match($pattern, '')) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector: Invalid regex pattern '.$pattern);
        }

        return $pattern;
    }
}
