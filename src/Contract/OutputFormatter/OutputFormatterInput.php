<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\OutputFormatter;

/**
 * @psalm-immutable
 */
final readonly class OutputFormatterInput
{
    public function __construct(
        public ?string $outputPath,
        public bool $reportSkipped,
        public bool $reportUncovered,
        public bool $failOnUncovered,
    ) {}
}
