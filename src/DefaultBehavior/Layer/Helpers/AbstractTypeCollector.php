<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer\Helpers;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;

abstract class AbstractTypeCollector extends RegexCollector
{
    abstract protected function getType(): ClassLikeType;

    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return false;
        }

        $isClassLike = ClassLikeType::TYPE_CLASSLIKE === $this->getType();
        $isSameType = $reference->type === $this->getType();

        return ($isClassLike || $isSameType) && $reference->getToken()->match($this->getValidatedPattern($config));
    }

    protected function getPattern(string $config): string
    {
        return '/'.$config.'/i';
    }
}
