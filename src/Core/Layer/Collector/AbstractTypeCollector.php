<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType;
use function is_string;
abstract class AbstractTypeCollector extends \Qossmic\Deptrac\Core\Layer\Collector\RegexCollector
{
    protected abstract function getType() : ClassLikeType;
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return \false;
        }
        $isClassLike = ClassLikeType::TYPE_CLASSLIKE === $this->getType();
        $isSameType = $reference->type === $this->getType();
        return ($isClassLike || $isSameType) && $reference->getToken()->match($this->getValidatedPattern($config));
    }
    protected function getPattern(array $config) : string
    {
        if (!isset($config['value']) || !is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(\sprintf('Collector "%s" needs the regex configuration.', $this->getType()->toString()));
        }
        return '/' . $config['value'] . '/i';
    }
}
