<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\OutputFormatter;

use DEPTRAC_INTERNAL\Symfony\Component\Console\Helper\TableSeparator;
/**
 * Wrapper around Symfony OutputStyle.
 */
interface OutputStyleInterface
{
    public function title(string $message) : void;
    public function section(string $message) : void;
    /**
     * @param string|string[] $message
     */
    public function success(string|array $message) : void;
    /**
     * @param string|string[] $message
     */
    public function error(string|array $message) : void;
    /**
     * @param string|string[] $message
     */
    public function warning(string|array $message) : void;
    /**
     * @param string|string[] $message
     */
    public function note(string|array $message) : void;
    /**
     * @param string|string[] $message
     */
    public function caution(string|array $message) : void;
    /**
     * @param string|array<string, string|int>|TableSeparator ...$list
     */
    public function definitionList(string|array|TableSeparator ...$list) : void;
    /**
     * @param mixed[] $headers
     * @param mixed[] $rows
     */
    public function table(array $headers, array $rows) : void;
    public function newLine(int $count = 1) : void;
    public function progressStart(int $max = 0) : void;
    public function progressAdvance(int $step = 1) : void;
    public function progressFinish() : void;
}
