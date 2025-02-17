<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Analyser;

use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Contract\Ast\TokenReferenceInterface;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Qossmic\Deptrac\Core\Ast\AstException;
use Qossmic\Deptrac\Core\Ast\AstMap\AstMap;
use Qossmic\Deptrac\Core\Ast\AstMapExtractor;
use Qossmic\Deptrac\Core\Dependency\TokenResolver;
use Qossmic\Deptrac\Core\Dependency\UnrecognizedTokenException;
use Qossmic\Deptrac\Core\Layer\LayerResolverInterface;
use function array_values;
use function ksort;
use function natcasesort;
use function str_contains;
class LayerForTokenAnalyser
{
    public function __construct(private readonly AstMapExtractor $astMapExtractor, private readonly TokenResolver $tokenResolver, private readonly LayerResolverInterface $layerResolver)
    {
    }
    /**
     * @return array<string, string[]>
     *
     * @throws AnalyserException
     */
    public function findLayerForToken(string $tokenName, \Qossmic\Deptrac\Core\Analyser\TokenType $tokenType) : array
    {
        try {
            $astMap = $this->astMapExtractor->extract();
            return match ($tokenType) {
                \Qossmic\Deptrac\Core\Analyser\TokenType::CLASS_LIKE => $this->findLayersForReferences($astMap->getClassLikeReferences(), $tokenName, $astMap),
                \Qossmic\Deptrac\Core\Analyser\TokenType::FUNCTION => $this->findLayersForReferences($astMap->getFunctionReferences(), $tokenName, $astMap),
                \Qossmic\Deptrac\Core\Analyser\TokenType::FILE => $this->findLayersForReferences($astMap->getFileReferences(), $tokenName, $astMap),
            };
        } catch (UnrecognizedTokenException $e) {
            throw \Qossmic\Deptrac\Core\Analyser\AnalyserException::unrecognizedToken($e);
        } catch (InvalidLayerDefinitionException $e) {
            throw \Qossmic\Deptrac\Core\Analyser\AnalyserException::invalidLayerDefinition($e);
        } catch (InvalidCollectorDefinitionException $e) {
            throw \Qossmic\Deptrac\Core\Analyser\AnalyserException::invalidCollectorDefinition($e);
        } catch (AstException $e) {
            throw \Qossmic\Deptrac\Core\Analyser\AnalyserException::failedAstParsing($e);
        } catch (CouldNotParseFileException $e) {
            throw \Qossmic\Deptrac\Core\Analyser\AnalyserException::couldNotParseFile($e);
        }
    }
    /**
     * @param TokenReferenceInterface[] $references
     *
     * @return array<string, string[]>
     *
     * @throws UnrecognizedTokenException
     * @throws InvalidLayerDefinitionException
     * @throws InvalidCollectorDefinitionException
     * @throws CouldNotParseFileException
     */
    private function findLayersForReferences(array $references, string $tokenName, AstMap $astMap) : array
    {
        if ([] === $references) {
            return [];
        }
        $layersForReference = [];
        foreach ($references as $reference) {
            if (!str_contains($reference->getToken()->toString(), $tokenName)) {
                continue;
            }
            $token = $this->tokenResolver->resolve($reference->getToken(), $astMap);
            $matchingLayers = \array_keys($this->layerResolver->getLayersForReference($token));
            natcasesort($matchingLayers);
            $layersForReference[$reference->getToken()->toString()] = array_values($matchingLayers);
        }
        ksort($layersForReference);
        return $layersForReference;
    }
}
