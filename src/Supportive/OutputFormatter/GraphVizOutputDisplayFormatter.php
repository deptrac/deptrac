<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\OutputFormatter;

use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Exception;
use DEPTRAC_INTERNAL\phpDocumentor\GraphViz\Graph;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputException;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputFormatterInput;
use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
/**
 * @internal
 */
final class GraphVizOutputDisplayFormatter extends \Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputFormatter
{
    /** @var positive-int */
    private const DELAY_OPEN = 2;
    public static function getName() : string
    {
        return 'graphviz-display';
    }
    protected function output(Graph $graph, OutputInterface $output, OutputFormatterInput $outputFormatterInput) : void
    {
        try {
            $filename = $this->getTempImage($graph);
            static $next = 0;
            if ($next > \microtime(\true)) {
                \sleep(self::DELAY_OPEN);
            }
            if ('WIN' === \strtoupper(\substr(\PHP_OS, 0, 3))) {
                \exec('start "" ' . \escapeshellarg($filename) . ' >NUL');
            } elseif ('DARWIN' === \strtoupper(\PHP_OS)) {
                \exec('open ' . \escapeshellarg($filename) . ' > /dev/null 2>&1 &');
            } else {
                \exec('xdg-open ' . \escapeshellarg($filename) . ' > /dev/null 2>&1 &');
            }
            $next = \microtime(\true) + (float) self::DELAY_OPEN;
        } catch (Exception $exception) {
            throw OutputException::withMessage('Unable to display output: ' . $exception->getMessage());
        }
    }
}
