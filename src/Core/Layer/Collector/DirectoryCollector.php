<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
final class DirectoryCollector extends \Deptrac\Deptrac\Core\Layer\Collector\RegexCollector
{
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        $filepath = $reference->getFilepath();
        if (null === $filepath) {
            return \false;
        }
        $validatedPattern = $this->getValidatedPattern($config);
        $normalizedPath = Path::normalize($filepath);
        return 1 === \preg_match($validatedPattern, $normalizedPath);
    }
    protected function getPattern(array $config) : string
    {
        if (!isset($config['value']) || !\is_string($config['value'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('DirectoryCollector needs the regex configuration.');
        }
        return '#' . $config['value'] . '#i';
    }
}
