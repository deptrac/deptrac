<?php

namespace Deptrac\Deptrac\Supportive\Console\Command;

class AnalyseOptions
{
    public function __construct(public readonly bool $noProgress, public readonly string $formatter, public readonly ?string $output, public readonly bool $reportSkipped, public readonly bool $reportUncovered, public readonly bool $failOnUncovered)
    {
    }
}
