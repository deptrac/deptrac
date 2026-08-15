<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Result;

use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;

/**
 * @psalm-immutable
 *
 * Represents a dependency that is NOT covered by the current configuration.
 */
final readonly class Uncovered implements RuleInterface
{
    public function __construct(
        private DependencyInterface $dependency,
        public string $layer,
    ) {}

    public function getDependency(): DependencyInterface
    {
        return $this->dependency;
    }
}
