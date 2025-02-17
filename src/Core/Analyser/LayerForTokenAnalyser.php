<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Ast\AstMap\AstMap;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;
use Deptrac\Deptrac\Core\Layer\LayerResolverInterface;
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
    public function findLayerForToken(string $tokenName, \Deptrac\Deptrac\Core\Analyser\TokenType $tokenType) : array
    {
        try {
            $astMap = $this->astMapExtractor->extract();
            return match ($tokenType) {
                \Deptrac\Deptrac\Core\Analyser\TokenType::CLASS_LIKE => $this->findLayersForReferences($astMap->getClassLikeReferences(), $tokenName, $astMap),
                \Deptrac\Deptrac\Core\Analyser\TokenType::FUNCTION => $this->findLayersForReferences($astMap->getFunctionReferences(), $tokenName, $astMap),
                \Deptrac\Deptrac\Core\Analyser\TokenType::FILE => $this->findLayersForReferences($astMap->getFileReferences(), $tokenName, $astMap),
            };
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
