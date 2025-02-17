<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Time;

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
    public function stop() : \Deptrac\Deptrac\Supportive\Time\Period
    {
        return \Deptrac\Deptrac\Supportive\Time\Period::stop($this);
    }
}
