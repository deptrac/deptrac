<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\Analyser;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event fired after the analysis is complete.
 *
 * Useful if you want to change the result of the analysis after it has
 * completed and before it is returned for output processing.
 */
final class PostProcessEvent extends Event
{
    public function __construct(private AnalysisResult $result) {}

    public function getResult(): AnalysisResult
    {
        return $this->result;
    }

    public function replaceResult(AnalysisResult $result): void
    {
        $this->result = $result;
    }
}
