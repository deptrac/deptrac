<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Ast\AstException;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Contract\Layer\LayerResolverInterface;
use Deptrac\Deptrac\Contract\Result\Uncovered;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Dependency\DependencyResolver;
use Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;

class LayerDependenciesAnalyser
{
    public function __construct(
        private readonly AstMapExtractor $astMapExtractor,
        private readonly TokenResolver $tokenResolver,
        private readonly DependencyResolver $dependencyResolver,
        private readonly LayerResolverInterface $layerResolver,
    ) {}

    /**
     * @return array<string, list<Uncovered>>
     *
     * @throws AnalyserException
     */
    public function getDependencies(string $layer, ?string $targetLayer): array
    {
        try {
            $result = [];
            $astMap = $this->astMapExtractor->extract();
            $dependencies = $this->dependencyResolver->resolve($astMap);
            foreach ($dependencies->getDependenciesAndInheritDependencies() as $dependency) {
                $dependerLayerNames = $this->layerResolver->getLayersForReference(
                    $this->tokenResolver->resolve($dependency->getDepender(), $astMap),
                );
                if (array_key_exists($layer, $dependerLayerNames)) {
                    $dependentLayerNames = $this->layerResolver->getLayersForReference(
                        $this->tokenResolver->resolve($dependency->getDependent(), $astMap),
                    );
                    foreach ($dependentLayerNames as $dependentLayerName => $_) {
                        if ($layer === $dependentLayerName
                            || (null !== $targetLayer
                                && $targetLayer !== $dependentLayerName)
                        ) {
                            continue;
                        }
                        $result[$dependentLayerName][] = new Uncovered($dependency, $dependentLayerName);
                    }
                }
            }

            return $result;
        } catch (InvalidEmitterConfigurationException $e) {
            throw AnalyserException::invalidEmitterConfiguration($e);
        } catch (UnrecognizedTokenException $e) {
            throw AnalyserException::unrecognizedToken($e);
        } catch (InvalidLayerDefinitionException $e) {
            throw AnalyserException::invalidLayerDefinition($e);
        } catch (InvalidCollectorDefinitionException $e) {
            throw AnalyserException::invalidCollectorDefinition($e);
        } catch (AstException $e) {
            throw AnalyserException::failedAstParsing($e);
        } catch (CouldNotParseFileException $e) {
            throw AnalyserException::couldNotParseFile($e);
        }
    }
}
