<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Analyser;

use Qossmic\Deptrac\Contract\Layer\LayerProvider;
use Qossmic\Deptrac\Contract\Result\SkippedViolation;
use Qossmic\Deptrac\Contract\Result\Violation;
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
     * @param array<string, list<string>> $skippedViolations
     */
    public function __construct(private readonly array $skippedViolations, public readonly LayerProvider $layerProvider)
    {
        $this->unmatchedSkippedViolation = $skippedViolations;
    }
    /**
     * @internal
     */
    public function shouldViolationBeSkipped(string $depender, string $dependent) : bool
    {
        $skippedViolation = $this->skippedViolations[$depender] ?? [];
        $matched = [] !== $skippedViolation && \in_array($dependent, $skippedViolation, \true);
        if (!$matched) {
            return \false;
        }
        if (\false !== ($key = \array_search($dependent, $this->unmatchedSkippedViolation[$depender], \true))) {
            unset($this->unmatchedSkippedViolation[$depender][$key]);
        }
        return \true;
    }
    /**
     * @return array<string, string[]> depender layer -> list<dependent layers>
     */
    public function unmatchedSkippedViolations() : array
    {
        return \array_filter($this->unmatchedSkippedViolation);
    }
    public function addSkippableViolation(\Qossmic\Deptrac\Contract\Analyser\ProcessEvent $event, \Qossmic\Deptrac\Contract\Analyser\AnalysisResult $result, string $dependentLayer, \Qossmic\Deptrac\Contract\Analyser\ViolationCreatingInterface $violationCreatingRule) : void
    {
        if ($this->shouldViolationBeSkipped($event->dependency->getDepender()->toString(), $event->dependency->getDependent()->toString())) {
            $result->addRule(new SkippedViolation($event->dependency, $event->dependerLayer, $dependentLayer));
        } else {
            $result->addRule(new Violation($event->dependency, $event->dependerLayer, $dependentLayer, $violationCreatingRule));
        }
    }
}
