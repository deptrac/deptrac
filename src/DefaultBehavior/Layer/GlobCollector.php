<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\DefaultBehavior\Layer\Helpers\RegexCollector;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Glob;

final class GlobCollector extends RegexCollector
{
    private readonly string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = Path::normalize($basePath);
    }

    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        $filepath = $reference->getFilepath();

        if (null === $filepath) {
            return false;
        }

        $validatedPattern = $this->getValidatedPattern($config);
        $normalizedPath = Path::normalize($filepath);
        /** @throws void */
        $relativeFilePath = Path::makeRelative($normalizedPath, $this->basePath);

        return 1 === preg_match($validatedPattern, $relativeFilePath);
    }

    protected function getPattern(string $config): string
    {
        return Glob::toRegex($config);
    }
}
