<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Supportive\Console\Command;

use Qossmic\Deptrac\Contract\OutputFormatter\OutputInterface;
use Qossmic\Deptrac\Core\Analyser\AnalyserException;
use Qossmic\Deptrac\Core\Analyser\LayerForTokenAnalyser;
use Qossmic\Deptrac\Core\Analyser\TokenType;
use function implode;
use function sprintf;
/**
 * @internal Should only be used by DebugTokenCommand
 */
final class DebugTokenRunner
{
    public function __construct(private readonly LayerForTokenAnalyser $analyser)
    {
    }
    /**
     * @throws CommandRunException
     */
    public function run(string $tokenName, TokenType $tokenType, OutputInterface $output) : void
    {
        try {
            $matches = $this->analyser->findLayerForToken($tokenName, $tokenType);
        } catch (AnalyserException $e) {
            throw \Qossmic\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($e);
        }
        if ([] === $matches) {
            $output->writeLineFormatted(sprintf('Could not find a token matching "%s"', $tokenName));
            return;
        }
        $headers = ['matching token', 'layers'];
        $rows = [];
        foreach ($matches as $token => $layers) {
            $rows[] = [$token, [] !== $layers ? implode(', ', $layers) : '---'];
        }
        $output->getStyle()->table($headers, $rows);
    }
}
