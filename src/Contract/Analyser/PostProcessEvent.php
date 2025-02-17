<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\Analyser;

use DEPTRAC_INTERNAL\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event fired after the analysis is complete.
 *
 * Useful if you want to change the result of the analysis after it has
 * completed and before it is returned for output processing.
 */
final class PostProcessEvent extends Event
{
    public function __construct(private \Qossmic\Deptrac\Contract\Analyser\AnalysisResult $result)
    {
    }
    public function getResult() : \Qossmic\Deptrac\Contract\Analyser\AnalysisResult
    {
        return $this->result;
    }
    public function replaceResult(\Qossmic\Deptrac\Contract\Analyser\AnalysisResult $result) : void
    {
        $this->result = $result;
    }
}
