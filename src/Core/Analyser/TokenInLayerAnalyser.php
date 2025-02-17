<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Analyser;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Config\EmitterType;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\Contract\Layer\InvalidLayerDefinitionException;
use Deptrac\Deptrac\Core\Ast\AstException;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\Dependency\UnrecognizedTokenException;
use Deptrac\Deptrac\Core\Layer\LayerResolverInterface;
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
        $this->tokenTypes = \array_filter(\array_map(static fn(string $emitterType): ?\Deptrac\Deptrac\Core\Analyser\TokenType => \Deptrac\Deptrac\Core\Analyser\TokenType::tryFromEmitterType(EmitterType::from($emitterType)), $config['types']));
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
            if (in_array(\Deptrac\Deptrac\Core\Analyser\TokenType::CLASS_LIKE, $this->tokenTypes, \true)) {
                foreach ($astMap->getClassLikeReferences() as $classReference) {
                    $classToken = $this->tokenResolver->resolve($classReference->getToken(), $astMap);
                    if (\array_key_exists($layer, $this->layerResolver->getLayersForReference($classToken))) {
                        $matchingTokens[] = [$classToken->getToken()->toString(), \Deptrac\Deptrac\Core\Analyser\TokenType::CLASS_LIKE->value];
                    }
                }
            }
            if (in_array(\Deptrac\Deptrac\Core\Analyser\TokenType::FUNCTION, $this->tokenTypes, \true)) {
                foreach ($astMap->getFunctionReferences() as $functionReference) {
                    $functionToken = $this->tokenResolver->resolve($functionReference->getToken(), $astMap);
                    if (\array_key_exists($layer, $this->layerResolver->getLayersForReference($functionToken))) {
                        $matchingTokens[] = [$functionToken->getToken()->toString(), \Deptrac\Deptrac\Core\Analyser\TokenType::FUNCTION->value];
                    }
                }
            }
            if (in_array(\Deptrac\Deptrac\Core\Analyser\TokenType::FILE, $this->tokenTypes, \true)) {
                foreach ($astMap->getFileReferences() as $fileReference) {
                    $fileToken = $this->tokenResolver->resolve($fileReference->getToken(), $astMap);
                    if (\array_key_exists($layer, $this->layerResolver->getLayersForReference($fileToken))) {
                        $matchingTokens[] = [$fileToken->getToken()->toString(), \Deptrac\Deptrac\Core\Analyser\TokenType::FILE->value];
                    }
                }
            }
            \uasort($matchingTokens, static fn(array $a, array $b): int => $a[0] <=> $b[0]);
            return array_values($matchingTokens);
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
