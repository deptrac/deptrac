<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\DependencyType;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Deptrac\Deptrac\Core\Ast\AstMap\File\FileReference;
use Deptrac\Deptrac\Core\Ast\AstMap\Function\FunctionReference;
use function str_contains;
class AttributeCollector implements CollectorInterface
{
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof FileReference && !$reference instanceof ClassLikeReference && !$reference instanceof FunctionReference) {
            return \false;
        }
        $match = $this->getSearchedSubstring($config);
        foreach ($reference->dependencies as $dependency) {
            if (DependencyType::ATTRIBUTE !== $dependency->context->dependencyType) {
                continue;
            }
            $usedAttribute = $dependency->token->toString();
            if (str_contains($usedAttribute, $match)) {
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
    private function getSearchedSubstring(array $config) : string
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('AttributeCollector needs the attribute name as a string.');
        }
        return $config['value'];
    }
}
