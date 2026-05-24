<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\Time;

use function hrtime;

/**
 * @psalm-immutable
 */
final readonly class Period
{
    private function __construct(
        public float|int $startedAt,
        public float|int $endedAt,
    ) {}

    /**
     * @psalm-pure
     */
    public static function stop(StartedPeriod $startedPeriod): self
    {
        return new self(
            $startedPeriod->startedAt,
            // @phpstan-ignore impure.functionCall (false positive)
            hrtime(true),
        );
    }

    public function toSeconds(): float
    {
        $duration = $this->endedAt - $this->startedAt;

        return $duration / 1e9;
    }
}
