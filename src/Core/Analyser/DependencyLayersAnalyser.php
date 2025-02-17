<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser;

use DEPTRAC_INTERNAL\Psr\EventDispatcher\EventDispatcherInterface;
use Deptrac\Deptrac\Contract\Analyser\AnalysisResult;
use Deptrac\Deptrac\Contract\Analyser\PostProcessEvent;
use Deptrac\Deptrac\Contract\Analyser\ProcessEvent;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Contract\Result\Warning;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Dependency\DependencyResolver;
use Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;
use Deptrac\Deptrac\Core\Layer\LayerResolverInterface;
use function count;
class DependencyLayersAnalyser
{
    public function __construct(private readonly AstMapExtractor $astMapExtractor, private readonly DependencyResolver $dependencyResolver, private readonly TokenResolver $tokenResolver, private readonly LayerResolverInterface $layerResolver, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }
    /**
     * @throws AnalyserException
     */
    public function analyse() : AnalysisResult
    {
        try {
            $astMap = $this->astMapExtractor->extract();
            $dependencies = $this->dependencyResolver->resolve($astMap);
            $result = new AnalysisResult();
            $warnings = [];
            foreach ($dependencies->getDependenciesAndInheritDependencies() as $dependency) {
                $depender = $dependency->getDepender();
                $dependerRef = $this->tokenResolver->resolve($depender, $astMap);
                $dependerLayers = \array_keys($this->layerResolver->getLayersForReference($dependerRef));
                if (!isset($warnings[$depender->toString()]) && count($dependerLayers) > 1) {
                    $warnings[$depender->toString()] = Warning::tokenIsInMoreThanOneLayer($depender->toString(), $dependerLayers);
                }
                $dependent = $dependency->getDependent();
                $dependentRef = $this->tokenResolver->resolve($dependent, $astMap);
                $dependentLayers = $this->layerResolver->getLayersForReference($dependentRef);
                foreach ($dependerLayers as $dependerLayer) {
                    $event = new ProcessEvent($dependency, $dependerRef, $dependerLayer, $dependentRef, $dependentLayers, $result);
                    $this->eventDispatcher->dispatch($event);
                    $result = $event->getResult();
                }
            }
            foreach ($warnings as $warning) {
                $result->addWarning($warning);
            }
            $event = new PostProcessEvent($result);
            $this->eventDispatcher->dispatch($event);
            return $event->getResult();
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
        }
    }
}
