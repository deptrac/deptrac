<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\OutputFormatter;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputException;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\DefaultBehavior\OutputFormatter\Helpers\GraphVizOutputFormatter;
use phpDocumentor\GraphViz\Exception;
use phpDocumentor\GraphViz\Graph;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

final class GraphVizOutputImageFormatter extends GraphVizOutputFormatter
{
    public static function getName(): string
    {
        return 'graphviz-image';
    }

    protected function output(Graph $graph, OutputInterface $output, OutputFormatterInput $outputFormatterInput): void
    {
        $dumpImagePath = $outputFormatterInput->outputPath;
        if (null === $dumpImagePath) {
            throw OutputException::withMessage("No '--output' defined for GraphViz formatter");
        }

        $imageFile = new SplFileInfo($dumpImagePath);
        $imagePathInfo = $imageFile->getPathInfo();

        if (null === $imagePathInfo) {
            throw OutputException::withMessage('Unable to dump image: Invalid or missing path.');
        }
        if (!$imagePathInfo->isWritable()) {
            throw OutputException::withMessage(sprintf('Unable to dump image: Path "%s" does not exist or is not writable.', Path::canonicalize($imagePathInfo->getPathname())));
        }
        try {
            $graph->export($imageFile->getExtension() ?: 'png', $imageFile->getPathname());
            $output->writeLineFormatted('<info>Image dumped to '.$imageFile->getPathname().'</info>');
        } catch (Exception $exception) {
            throw OutputException::withMessage('Unable to display output: '.$exception->getMessage());
        }
    }
}
