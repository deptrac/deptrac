<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Exception;
use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Graph;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputException;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use function base64_encode;
use function file_get_contents;
/**
 * @internal
 */
final class GraphVizOutputHtmlFormatter extends \Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputFormatter
{
    public static function getName() : string
    {
        return 'graphviz-html';
    }
    protected function output(Graph $graph, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        $dumpHtmlPath = $outputFormatterInput->outputPath;
        if (null === $dumpHtmlPath) {
            throw OutputException::withMessage("No '--output' defined for GraphViz formatter");
        }
        try {
            $filename = $this->getTempImage($graph);
            $imageData = file_get_contents($filename);
            if (\false === $imageData) {
                throw OutputException::withMessage('Unable to create temp file for output.');
            }
            \file_put_contents($dumpHtmlPath, '<img src="data:image/png;base64,' . base64_encode($imageData) . '" />');
            $output->writeLineFormatted('<info>HTML dumped to ' . \realpath($dumpHtmlPath) . '</info>');
        } catch (Exception $exception) {
            throw OutputException::withMessage('Unable to generate HTML file: ' . $exception->getMessage());
        } finally {
            if (isset($filename)) {
                \unlink($filename);
            }
        }
    }
}
