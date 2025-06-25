<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Analyser;

use Deptrac\Deptrac\Contract\Layer\LayerProviderInterface;
use Deptrac\Deptrac\Contract\OutputFormatter\BaselineMapperInterface;
use Deptrac\Deptrac\Contract\Result\SkippedViolation;
use Deptrac\Deptrac\Contract\Result\Violation;

/**
 * Utility class for managing adding violations that could be skipped.
 */
final class EventHelper
{
    /**
     * @var array<string, list<string>> depender layer -> list<dependent layers>
     */
    private array $unmatchedSkippedViolation;

    /**
     * @var array<string, list<string>>
     */
    private readonly array $skippedViolations;

    public function __construct(
        public readonly LayerProviderInterface $layerProvider,
        private readonly BaselineMapperInterface $baselineMapper,
    ) {
        $this->skippedViolations = $this->baselineMapper->loadViolations();
        $this->unmatchedSkippedViolation = $this->skippedViolations;
    }

    /**
     * @internal
     */
    public function shouldViolationBeSkipped(string $depender, string $dependent): bool
    {
        $skippedViolation = $this->skippedViolations[$depender] ?? [];
        $matched = [] !== $skippedViolation && in_array($dependent, $skippedViolation, true);

        if (!$matched) {
            return false;
        }

        if (false !== ($key = array_search($dependent, $this->unmatchedSkippedViolation[$depender], true))) {
            unset($this->unmatchedSkippedViolation[$depender][$key]);
        }

        return true;
    }

    /**
     * @return array<string, string[]> depender layer -> list<dependent layers>
     */
    public function unmatchedSkippedViolations(): array
    {
        return array_filter($this->unmatchedSkippedViolation);
    }

    public function addSkippableViolation(ProcessEvent $event, AnalysisResult $result, string $dependentLayer, ViolationCreatingInterface $violationCreatingRule): void
    {
        if ($this->shouldViolationBeSkipped(
            $event->dependency->getDepender()
                ->toString(),
            $event->dependency->getDependent()
                ->toString()
        )
        ) {
            $result->addRule(new SkippedViolation($event->dependency, $event->dependerLayer, $dependentLayer));
        } else {
            $result->addRule(new Violation($event->dependency, $event->dependerLayer, $dependentLayer, $violationCreatingRule));
        }
    }
}
