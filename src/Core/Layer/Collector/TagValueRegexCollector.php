<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TaggedTokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
final class TagValueRegexCollector extends \Deptrac\Deptrac\Core\Layer\Collector\RegexCollector
{
    /**
     * @param array<string, bool|string|array<string, string>> $config
     */
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof TaggedTokenReferenceInterface) {
            return \false;
        }
        $tagLines = $reference->getTagLines($this->getTagName($config));
        $pattern = $this->getValidatedPattern($config);
        if (null === $tagLines || [] === $tagLines) {
            return \false;
        }
        foreach ($tagLines as $line) {
            if (\preg_match($pattern, $line)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<string, bool|string|array<string, string>|null> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    protected function getTagName(array $config) : string
    {
        if (!isset($config['tag']) || !\is_string($config['tag'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector needs the tag name.');
        }
        if (!\preg_match('/^@[-\\w]+$/', $config['tag'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector needs a valid tag name.');
        }
        return $config['tag'];
    }
    protected function getPattern(array $config) : string
    {
        if (!isset($config['value'])) {
            return '/^.?/';
            // any string
        }
        if (!\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('TagValueRegexCollector regex value must be a string.');
        }
        return $config['value'];
    }
}
