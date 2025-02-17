<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Contract\OutputFormatter;

use Qossmic\Deptrac\Contract\Result\OutputResult;
interface OutputFormatterInterface
{
    /**
     * @return string used as an identifier to access to the formatter or to display something more user-friendly to the
     *                user when referring to the formatter
     *
     * @example "graphviz"
     */
    public static function getName() : string;
    /**
     * Renders the final result.
     *
     * @throws OutputException
     */
    public function finish(OutputResult $result, \Qossmic\Deptrac\Contract\OutputFormatter\OutputInterface $output, \Qossmic\Deptrac\Contract\OutputFormatter\OutputFormatterInput $outputFormatterInput) : void;
}
