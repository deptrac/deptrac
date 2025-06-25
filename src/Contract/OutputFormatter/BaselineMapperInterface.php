<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Contract\OutputFormatter;

interface BaselineMapperInterface
{
    /**
     * Maps a grouped list of violations to a format that will be stored to a
     * file by the `baseline` formatter.
     *
     * @param array<string,list<string>> $groupedViolations
     */
    public function fromPHPListToString(array $groupedViolations): string;

    /**
     * Load the existing violation to ignore by custom mapper logic.
     *
     * @return array<string,list<string>>
     */
    public function loadViolations(): array;
}
