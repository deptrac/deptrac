<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\CircularReferenceException;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Contract\Layer\LayerProvider;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Dependency\DependencyResolver;
use Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;
use Deptrac\Deptrac\Core\Layer\LayerResolverInterface;
class RulesetUsageAnalyser
{
    /**
     * @param array<array{name:string}> $layers
     */
    public function __construct(private readonly LayerProvider $layerProvider, private readonly LayerResolverInterface $layerResolver, private readonly AstMapExtractor $astMapExtractor, private readonly DependencyResolver $dependencyResolver, private readonly TokenResolver $tokenResolver, private readonly array $layers)
    {
    }
    /**
     * @return array<string, array<string, int>>
     *
     * @throws AnalyserException
     */
    public function analyse() : array
    {
        try {
            return $this->findRulesetUsages($this->rulesetResolution());
        } catch (InvalidEmitterConfigurationException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::invalidEmitterConfiguration($e);
        } catch (UnrecognizedTokenException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::unrecognizedToken($e);
        } catch (InvalidLayerDefinitionException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::invalidLayerDefinition($e);
        } catch (InvalidCollectorDefinitionException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::invalidCollectorDefinition($e);
        } catch (AstException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::failedAstParsing($e);
        } catch (CouldNotParseFileException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::couldNotParseFile($e);
        } catch (CircularReferenceException $e) {
            throw \Deptrac\Deptrac\Core\Analyser\AnalyserException::circularReference($e);
        }
    }
    /**
     * @return array<string, array<string, 0>> sourceLayer -> (targetLayer -> 0)
     *
     * @throws CircularReferenceException
     */
    private function rulesetResolution() : array
    {
        $layerNames = [];
        foreach (\array_map(static fn(array $layerDef): string => $layerDef['name'], $this->layers) as $sourceLayerName) {
            foreach ($this->layerProvider->getAllowedLayers($sourceLayerName) as $destinationLayerName) {
                $layerNames[$sourceLayerName][$destinationLayerName] = 0;
            }
        }
        return $layerNames;
    }
    /**
     * @param array<string, array<string, 0>> $rulesets
     *
     * @return array<string, array<string, int>>
     *
     * @throws AstException
     * @throws CouldNotParseFileException
     * @throws InvalidEmitterConfigurationException
     * @throws InvalidCollectorDefinitionException
     * @throws InvalidLayerDefinitionException
     * @throws UnrecognizedTokenException
     */
    private function findRulesetUsages(array $rulesets) : array
    {
        $astMap = $this->astMapExtractor->extract();
        $dependencyResult = $this->dependencyResolver->resolve($astMap);
        foreach ($dependencyResult->getDependenciesAndInheritDependencies() as $dependency) {
            $dependerLayerNames = $this->layerResolver->getLayersForReference($this->tokenResolver->resolve($dependency->getDepender(), $astMap));
            foreach ($dependerLayerNames as $dependerLayerName => $_) {
                $dependentLayerNames = $this->layerResolver->getLayersForReference($this->tokenResolver->resolve($dependency->getDependent(), $astMap));
                foreach ($dependentLayerNames as $dependentLayerName => $__) {
                    if (\array_key_exists($dependerLayerName, $rulesets) && \array_key_exists($dependentLayerName, $rulesets[$dependerLayerName])) {
                        ++$rulesets[$dependerLayerName][$dependentLayerName];
                    }
                }
            }
        }
        return $rulesets;
    }
}
