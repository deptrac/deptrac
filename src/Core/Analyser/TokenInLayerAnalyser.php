<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Analyser;

use Qossmic\Deptrac\Contract\Ast\CouldNotParseFileException;
use Qossmic\Deptrac\Contract\Config\EmitterType;
use Qossmic\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Qossmic\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Qossmic\Deptrac\Core\Ast\AstException;
use Qossmic\Deptrac\Core\Ast\AstMapExtractor;
use Qossmic\Deptrac\Core\Dependency\TokenResolver;
use Qossmic\Deptrac\Core\Dependency\UnrecognizedTokenException;
use Qossmic\Deptrac\Core\Layer\LayerResolverInterface;
use function array_values;
use function in_array;
class TokenInLayerAnalyser
{
    /**
     * @var array<TokenType>
     */
    private readonly array $tokenTypes;
    /**
     * @param array{types: array<string>} $config
     */
    public function __construct(private readonly AstMapExtractor $astMapExtractor, private readonly TokenResolver $tokenResolver, private readonly LayerResolverInterface $layerResolver, array $config)
    {
        $this->tokenTypes = \array_filter(\array_map(static fn(string $emitterType): ?\Qossmic\Deptrac\Core\Analyser\TokenType => \Qossmic\Deptrac\Core\Analyser\TokenType::tryFromEmitterType(EmitterType::from($emitterType)), $config['types']));
    }
    /**
     * @return list<array{string, string}>
     *
     * @throws AnalyserException
     */
    public function findTokensInLayer(string $layer) : array
    {
        try {
            $astMap = $this->astMapExtractor->extract();
            $matchingTokens = [];
            if (in_array(\Qossmic\Deptrac\Core\Analyser\TokenType::CLASS_LIKE, $this->tokenTypes, \true)) {
                foreach ($astMap->getClassLikeReferences() as $classReference) {
                    $classToken = $this->tokenResolver->resolve($classReference->getToken(), $astMap);
                    if (\array_key_exists($layer, $this->layerResolver->getLayersForReference($classToken))) {
                        $matchingTokens[] = [$classToken->getToken()->toString(), \Qossmic\Deptrac\Core\Analyser\TokenType::CLASS_LIKE->value];
                    }
                }
            }
            if (in_array(\Qossmic\Deptrac\Core\Analyser\TokenType::FUNCTION, $this->tokenTypes, \true)) {
                foreach ($astMap->getFunctionReferences() as $functionReference) {
                    $functionToken = $this->tokenResolver->resolve($functionReference->getToken(), $astMap);
                    if (\array_key_exists($layer, $this->layerResolver->getLayersForReference($functionToken))) {
                        $matchingTokens[] = [$functionToken->getToken()->toString(), \Qossmic\Deptrac\Core\Analyser\TokenType::FUNCTION->value];
                    }
                }
            }
            if (in_array(\Qossmic\Deptrac\Core\Analyser\TokenType::FILE, $this->tokenTypes, \true)) {
                foreach ($astMap->getFileReferences() as $fileReference) {
                    $fileToken = $this->tokenResolver->resolve($fileReference->getToken(), $astMap);
                    if (\array_key_exists($layer, $this->layerResolver->getLayersForReference($fileToken))) {
                        $matchingTokens[] = [$fileToken->getToken()->toString(), \Qossmic\Deptrac\Core\Analyser\TokenType::FILE->value];
                    }
                }
            }
            \uasort($matchingTokens, static fn(array $a, array $b): int => $a[0] <=> $b[0]);
            return array_values($matchingTokens);
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
}
