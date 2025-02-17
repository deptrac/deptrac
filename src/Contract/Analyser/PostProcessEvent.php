<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Analyser;

use DEPTRAC_INTERNAL\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event fired after the analysis is complete.
 *
 * Useful if you want to change the result of the analysis after it has
 * completed and before it is returned for output processing.
 */
final class PostProcessEvent extends Event
{
    public function __construct(private \Deptrac\Deptrac\Contract\Analyser\AnalysisResult $result)
    {
    }
    public function getResult() : \Deptrac\Deptrac\Contract\Analyser\AnalysisResult
    {
        return $this->result;
    }
    public function replaceResult(\Deptrac\Deptrac\Contract\Analyser\AnalysisResult $result) : void
    {
        $this->result = $result;
    }
}
