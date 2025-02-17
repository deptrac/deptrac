<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Core\Analyser\AnalyserException;
use Deptrac\Deptrac\Core\Analyser\UnassignedTokenAnalyser;
/**
 * @internal Should only be used by DebugUnassignedCommand
 */
final class DebugUnassignedRunner
{
    public function __construct(private readonly UnassignedTokenAnalyser $analyser)
    {
    }
    /**
     * @return bool are there any unassigned tokens?
     *
     * @throws CommandRunException
     */
    public function run(OutputInterface $output) : bool
    {
        try {
            $unassignedTokens = $this->analyser->findUnassignedTokens();
        } catch (AnalyserException $e) {
            throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($e);
        }
        if ([] === $unassignedTokens) {
            $output->writeLineFormatted('There are no unassigned tokens.');
            return \false;
        }
        $output->writeLineFormatted($unassignedTokens);
        return \true;
    }
}
