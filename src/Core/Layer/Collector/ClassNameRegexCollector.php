<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
final class ClassNameRegexCollector extends \Deptrac\Deptrac\Core\Layer\Collector\RegexCollector
{
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return \false;
        }
        return $reference->getToken()->match($this->getValidatedPattern($config));
    }
    protected function getPattern(array $config) : string
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ClassNameRegexCollector needs the regex configuration.');
        }
        return $config['value'];
    }
}
