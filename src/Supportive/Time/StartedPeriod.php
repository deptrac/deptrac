<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Time;

/**
 * @psalm-immutable
 */
final readonly class StartedPeriod
{
    private function __construct(
        public float|int $startedAt,
    ) {}

    public static function start(): self
    {
        return new self(
            hrtime(true),
        );
    }

    public function stop(): Period
    {
        return Period::stop($this);
    }
}
