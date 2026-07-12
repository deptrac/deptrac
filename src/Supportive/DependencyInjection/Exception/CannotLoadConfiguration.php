<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\DependencyInjection\Exception;

use Deptrac\Deptrac\Contract\ExceptionInterface;
use RuntimeException;

class CannotLoadConfiguration extends RuntimeException implements ExceptionInterface
{
    public static function fromConfig(string $filename, string $message): self
    {
        return new self(sprintf('Could not load %s. Reason: %s', $filename, $message));
    }

    public static function fromServices(string $filename, string $message): self
    {
        return new self(sprintf('Could not load %s. Reason: %s', $filename, $message));
    }

    public static function fromCache(string $filename, string $message): self
    {
        return new self(sprintf('Could not load %s. Reason: %s', $filename, $message));
    }

    public static function cannotFind(): self
    {
        return new self(<<<'MSG'
            No Deptrac config found. Expected one of: "deptrac.php" or "deptrac.yaml" in the current directory.
            Use "-c <path>" to specify a config file explicitly.
        MSG);
    }
}
