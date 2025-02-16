<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeToken;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
final class ExtendsCollector implements CollectorInterface
{
    private readonly AstMap $astMap;
    /**
     * @throws AstException
     */
    public function __construct(AstMapExtractor $astMapExtractor)
    {
        $this->astMap = $astMapExtractor->extract();
    }
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return \false;
        }
        $interfaceName = $this->getInterfaceName($config);
        foreach ($this->astMap->getClassInherits($reference->getToken()) as $inherit) {
            if (AstInheritType::EXTENDS === $inherit->type && $inherit->classLikeName->equals($interfaceName)) {
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
    private function getInterfaceName(array $config) : ClassLikeToken
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ExtendsCollector needs the interface or class name as a string.');
        }
        return ClassLikeToken::fromFQCN($config['value']);
    }
}
