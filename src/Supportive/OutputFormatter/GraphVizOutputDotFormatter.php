<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Graph;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputException;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
/**
 * @internal
 */
final class GraphVizOutputDotFormatter extends \Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputFormatter
{
    public static function getName() : string
    {
        return 'graphviz-dot';
    }
    protected function output(Graph $graph, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $dumpDotPath = $outputFormatterInput->outputPath;
        if (null === $dumpDotPath) {
            throw OutputException::withMessage("No '--output' defined for GraphViz formatter");
        }
        \file_put_contents($dumpDotPath, (string) $graph);
        $output->writeLineFormatted('<info>Script dumped to ' . \realpath($dumpDotPath) . '</info>');
    }
}
