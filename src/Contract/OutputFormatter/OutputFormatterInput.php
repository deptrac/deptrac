<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\OutputFormatter;

/**
 * @psalm-immutable
 */
final class OutputFormatterInput
{
    public function __construct(public readonly ?string $outputPath, public readonly bool $reportSkipped, public readonly bool $reportUncovered, public readonly bool $failOnUncovered)
    {
    }
}
