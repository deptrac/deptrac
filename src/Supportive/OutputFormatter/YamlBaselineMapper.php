<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use Deptrac\Deptrac\Contract\OutputFormatter\BaselineMapperInterface;
use Symfony\Component\Yaml\Yaml;

final class YamlBaselineMapper implements BaselineMapperInterface
{
    /**
     * @param array<string, list<string>> $skippedViolations
     */
    public function __construct(
        private readonly array $skippedViolations,
    ) {}

    public function fromPHPListToString(array $groupedViolations): string
    {
        return Yaml::dump(
            [
                'deptrac' => [
                    'skip_violations' => $groupedViolations,
                ],
            ],
            4,
            2
        );
    }

    public function loadViolations(): array
    {
        return $this->skippedViolations;
    }
}
