<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\Console\Command;

use Deptrac\Deptrac\Contract\OutputFormatter\OutputInterface;
use Deptrac\Deptrac\Core\Analyser\AnalyserException;
use Deptrac\Deptrac\Core\Analyser\TokenInLayerAnalyser;
use function array_map;
/**
 * @internal Should only be used by DebugLayerCommand
 */
final class DebugLayerRunner
{
    /**
     * @param array<array{name: string, collectors: array<array<string, string|array<string, string>>>}> $layers
     */
    public function __construct(private readonly TokenInLayerAnalyser $analyser, private readonly array $layers)
    {
    }
    /**
     * @throws CommandRunException
     */
    public function run(?string $layer, OutputInterface $output) : void
    {
        $debugLayers = $layer ? [$layer] : array_map(static fn(array $layer): string => $layer['name'], $this->layers);
        try {
            foreach ($debugLayers as $debugLayer) {
                $output->getStyle()->table([$debugLayer, 'Token Type'], $this->analyser->findTokensInLayer($debugLayer));
            }
        } catch (AnalyserException $e) {
            throw \Deptrac\Deptrac\Supportive\Console\Command\CommandRunException::analyserException($e);
        }
    }
}
