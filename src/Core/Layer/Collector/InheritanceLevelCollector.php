<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
final class InheritanceLevelCollector implements CollectorInterface
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
        $classInherits = $this->astMap->getClassInherits($reference->getToken());
        if (!isset($config['value']) || !\is_numeric($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('InheritanceLevelCollector needs inheritance depth as int.');
        }
        $depth = (int) $config['value'];
        foreach ($classInherits as $classInherit) {
            if (\count($classInherit->getPath()) >= $depth) {
                return \true;
            }
        }
        return \false;
    }
}
