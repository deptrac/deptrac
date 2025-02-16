<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Core\Dependency\Dependency;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
use function array_map;
use function array_merge;
use function explode;
final class UsesDependencyEmitter implements \Deptrac\Deptrac\Core\Dependency\Emitter\DependencyEmitterInterface
{
    public function getName() : string
    {
        return 'UsesDependencyEmitter';
    }
    public function applyDependencies(AstMap $astMap, DependencyList $dependencyList) : void
    {
        $references = array_merge($astMap->getClassLikeReferences(), $astMap->getFunctionReferences());
        $referencesFQDN = array_map(static fn($ref): string => $ref->getToken()->toString(), $references);
        $FQDNIndex = new \Deptrac\Deptrac\Core\Dependency\Emitter\FQDNIndexNode();
        foreach ($referencesFQDN as $reference) {
            $path = explode('\\', $reference);
            $FQDNIndex->setNestedNode($path);
        }
        foreach ($astMap->getFileReferences() as $fileReference) {
            foreach ($fileReference->classLikeReferences as $astClassReference) {
                foreach ($fileReference->dependencies as $emittedDependency) {
                    if (DependencyType::USE === $emittedDependency->context->dependencyType && $this->isFQDN($emittedDependency, $FQDNIndex)) {
                        $dependencyList->addDependency(new Dependency($astClassReference->getToken(), $emittedDependency->token, $emittedDependency->context));
                    }
                }
            }
        }
    }
    private function isFQDN(DependencyToken $dependency, \Deptrac\Deptrac\Core\Dependency\Emitter\FQDNIndexNode $FQDNIndex) : bool
    {
        $dependencyFQDN = $dependency->token->toString();
        $path = explode('\\', $dependencyFQDN);
        $value = $FQDNIndex->getNestedNode($path);
        if (null === $value) {
            return \true;
        }
        return $value->isFQDN();
    }
}
