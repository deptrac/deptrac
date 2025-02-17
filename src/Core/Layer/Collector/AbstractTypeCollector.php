<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeType;
use function is_string;
abstract class AbstractTypeCollector extends \Deptrac\Deptrac\Core\Layer\Collector\RegexCollector
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
