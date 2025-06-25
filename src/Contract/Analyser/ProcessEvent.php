<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Analyser;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that is triggered on every found dependency.
 *
 * Used to apply rules on the found dependencies.
 */
final class ProcessEvent extends Event
{
    /**
     * @param array<string, bool> $dependentLayers layer name and whether the dependency is public(true) or private(false)
     */
    public function __construct(
        public readonly DependencyInterface $dependency,
        public readonly TokenReferenceInterface $dependerReference,
        public readonly string $dependerLayer,
        public readonly TokenReferenceInterface $dependentReference,
        public readonly array $dependentLayers,
        private AnalysisResult $result,
    ) {}

    public function getResult(): AnalysisResult
    {
        return $this->result;
    }

    public function replaceResult(AnalysisResult $ruleset): void
    {
        $this->result = $ruleset;
    }
}
