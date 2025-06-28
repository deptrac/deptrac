<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\ParserInterface;
use Deptrac\Deptrac\DefaultBehavior\Layer\Helpers\RegexCollector;

final class MethodCollector extends RegexCollector
{
    public function __construct(private readonly ParserInterface $astParser) {}

    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return false;
        }

        $pattern = $this->getValidatedPattern($config);

        $classMethods = $this->astParser->getMethodNamesForClassLikeReference($reference);

        foreach ($classMethods as $classMethod) {
            if (1 === preg_match($pattern, $classMethod)) {
                return true;
            }
        }

        return false;
    }

    protected function getPattern(string $config): string
    {
        return '/'.$config.'/i';
    }
}
