<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Time;

use function hrtime;
/**
 * @psalm-immutable
 */
final class Period
{
    private function __construct(public readonly float|int $startedAt, public readonly float|int $endedAt)
    {
    }
    /**
     * @psalm-pure
     */
    public static function stop(\Deptrac\Deptrac\Supportive\Time\StartedPeriod $startedPeriod) : self
    {
        return new self($startedPeriod->startedAt, hrtime(\true));
    }
    public function toSeconds() : float
    {
        $duration = $this->endedAt - $this->startedAt;
        return $duration / 1000000000.0;
    }
}
