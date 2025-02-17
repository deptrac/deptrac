<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\Time;

/**
 * @psalm-immutable
 */
final class StartedPeriod
{
    private function __construct(public readonly float|int $startedAt)
    {
    }
    public static function start() : self
    {
        return new self(\hrtime(\true));
    }
    public function stop() : \Qossmic\Deptrac\Supportive\Time\Period
    {
        return \Qossmic\Deptrac\Supportive\Time\Period::stop($this);
    }
}
