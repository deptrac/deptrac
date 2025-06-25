<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Ast\AstMap;

use Symfony\Component\Filesystem\Path;

final class FileToken implements TokenInterface
{
    public readonly string $path;

    public function __construct(string $path)
    {
        $this->path = Path::normalize($path);
    }

    public function toString(): string
    {
        $wd = getcwd();

        if (false !== $wd) {
            $wd = Path::normalize($wd);
        }

        if (false !== $wd && str_starts_with($this->path, $wd)) {
            return substr($this->path, strlen($wd));
        }

        return $this->path;
    }

    public function equals(TokenInterface $token): bool
    {
        return $token instanceof self && $this->toString() === $token->toString();
    }
}
