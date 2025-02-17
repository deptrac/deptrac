<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Layer\Collector;

use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Core\Ast\AstMap\ClassLike\ClassLikeReference;
use Qossmic\Deptrac\Core\Ast\Parser\NikicPhpParser\NikicPhpParser;
final class MethodCollector extends \Qossmic\Deptrac\Core\Layer\Collector\RegexCollector
{
    public function __construct(private readonly NikicPhpParser $astParser)
    {
    }
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!$reference instanceof ClassLikeReference) {
            return \false;
        }
        $pattern = $this->getValidatedPattern($config);
        $classLike = $this->astParser->getNodeForClassLikeReference($reference);
        if (null === $classLike) {
            return \false;
        }
        foreach ($classLike->getMethods() as $classMethod) {
            if (1 === \preg_match($pattern, (string) $classMethod->name)) {
                return \true;
            }
        }
        return \false;
    }
    protected function getPattern(array $config) : string
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('MethodCollector needs the name configuration.');
        }
        return '/' . $config['value'] . '/i';
    }
}
