<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Core\Analyser\AnalyserException;
use Deptrac\Deptrac\Core\Analyser\LayerForTokenAnalyser;
use Deptrac\Deptrac\Core\Analyser\TokenType;
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
            throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($e);
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
