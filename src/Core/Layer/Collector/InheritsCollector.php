<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
use Qossmic\Deptrac\Contract\Layer\CollectorInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Core\Ast\AstException;
use Qossmic\Deptrac\Core\Ast\AstMap\AstMap;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
use Qossmic\Deptrac\Core\Ast\AstMapExtractor;
final class InheritsCollector implements CollectorInterface
{
    private readonly AstMap $astMap;
    /**
     * @throws AstException
     */
    public function __construct(private AstMapExtractor $astMapExtractor)
    {
        $this->astMap = $this->astMapExtractor->extract();
    }
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return \false;
        }
        $classLikeName = $this->getClassLikeName($config);
        foreach ($this->astMap->getClassInherits($reference->getToken()) as $inherit) {
            if ($inherit->classLikeName->equals($classLikeName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param array<string, bool|string|array<string, string>> $config
     *
     * @throws InvalidCollectorDefinitionException
     */
    private function getClassLikeName(array $config) : ClassLikeToken
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('InheritsCollector needs the interface, trait or class name as a string.');
        }
        return ClassLikeToken::fromFQCN($config['value']);
    }
}
