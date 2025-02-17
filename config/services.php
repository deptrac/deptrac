<?php

declare (strict_types=1);
namespace DEPTRAC_INTERNAL;

use DEPTRAC_INTERNAL\PhpParser\Lexer;
use DEPTRAC_INTERNAL\PhpParser\Parser;
use DEPTRAC_INTERNAL\PhpParser\ParserFactory;
use DEPTRAC_INTERNAL\Psr\EventDispatcher\EventDispatcherInterface;
use Deptrac\Deptrac\Contract\Analyser\EventHelper;
use Deptrac\Deptrac\Contract\Config\CollectorType;
use Deptrac\Deptrac\Contract\Config\EmitterType;
use Deptrac\Deptrac\Contract\Layer\LayerProvider;
use Deptrac\Deptrac\Core\Analyser\DependencyLayersAnalyser;
use Deptrac\Deptrac\Core\Analyser\EventHandler\AllowDependencyHandler;
use Deptrac\Deptrac\Core\Analyser\EventHandler\DependsOnDisallowedLayer;
use Deptrac\Deptrac\Core\Analyser\EventHandler\DependsOnInternalToken;
use Deptrac\Deptrac\Core\Analyser\EventHandler\DependsOnPrivateLayer;
use Deptrac\Deptrac\Core\Analyser\EventHandler\MatchingLayersHandler;
use Deptrac\Deptrac\Core\Analyser\EventHandler\UncoveredDependentHandler;
use Deptrac\Deptrac\Core\Analyser\EventHandler\UnmatchedSkippedViolations;
use Deptrac\Deptrac\Core\Analyser\LayerDependenciesAnalyser;
use Deptrac\Deptrac\Core\Analyser\LayerForTokenAnalyser;
use Deptrac\Deptrac\Core\Analyser\RulesetUsageAnalyser;
use Deptrac\Deptrac\Core\Analyser\TokenInLayerAnalyser;
use Deptrac\Deptrac\Core\Analyser\UnassignedTokenAnalyser;
use Deptrac\Deptrac\Core\Ast\AstLoader;
use Deptrac\Deptrac\Core\Ast\AstMapExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\AnnotationReferenceExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\AnonymousClassExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\ClassConstantExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\FunctionCallResolver;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\FunctionLikeExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\KeywordExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\PropertyExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\StaticExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\Extractors\VariableExtractor;
use Deptrac\Deptrac\Core\Ast\Parser\NikicPhpParser\NikicPhpParser;
use Deptrac\Deptrac\Core\Ast\Parser\ParserInterface;
use Deptrac\Deptrac\Core\Ast\Parser\TypeResolver;
use Deptrac\Deptrac\Core\Dependency\DependencyResolver;
use Deptrac\Deptrac\Core\Dependency\Emitter\ClassDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\Emitter\ClassSuperglobalDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\Emitter\FileDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\Emitter\FunctionCallDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\Emitter\FunctionDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\Emitter\FunctionSuperglobalDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\Emitter\UsesDependencyEmitter;
use Deptrac\Deptrac\Core\Dependency\InheritanceFlattener;
use Deptrac\Deptrac\Core\Dependency\TokenResolver;
use Deptrac\Deptrac\Core\InputCollector\FileInputCollector;
use Deptrac\Deptrac\Core\InputCollector\InputCollectorInterface;
use Deptrac\Deptrac\Core\Layer\Collector\AttributeCollector;
use Deptrac\Deptrac\Core\Layer\Collector\BoolCollector;
use Deptrac\Deptrac\Core\Layer\Collector\ClassCollector;
use Deptrac\Deptrac\Core\Layer\Collector\ClassLikeCollector;
use Deptrac\Deptrac\Core\Layer\Collector\ClassNameRegexCollector;
use Deptrac\Deptrac\Core\Layer\Collector\CollectorProvider;
use Deptrac\Deptrac\Core\Layer\Collector\CollectorResolver;
use Deptrac\Deptrac\Core\Layer\Collector\CollectorResolverInterface;
use Deptrac\Deptrac\Core\Layer\Collector\ComposerCollector;
use Deptrac\Deptrac\Core\Layer\Collector\DirectoryCollector;
use Deptrac\Deptrac\Core\Layer\Collector\ExtendsCollector;
use Deptrac\Deptrac\Core\Layer\Collector\FunctionNameCollector;
use Deptrac\Deptrac\Core\Layer\Collector\GlobCollector;
use Deptrac\Deptrac\Core\Layer\Collector\ImplementsCollector;
use Deptrac\Deptrac\Core\Layer\Collector\InheritanceLevelCollector;
use Deptrac\Deptrac\Core\Layer\Collector\InheritsCollector;
use Deptrac\Deptrac\Core\Layer\Collector\InterfaceCollector;
use Deptrac\Deptrac\Core\Layer\Collector\LayerCollector;
use Deptrac\Deptrac\Core\Layer\Collector\MethodCollector;
use Deptrac\Deptrac\Core\Layer\Collector\PhpInternalCollector;
use Deptrac\Deptrac\Core\Layer\Collector\SuperglobalCollector;
use Deptrac\Deptrac\Core\Layer\Collector\TagValueRegexCollector;
use Deptrac\Deptrac\Core\Layer\Collector\TraitCollector;
use Deptrac\Deptrac\Core\Layer\Collector\UsesCollector;
use Deptrac\Deptrac\Core\Layer\LayerResolver;
use Deptrac\Deptrac\Core\Layer\LayerResolverInterface;
use Deptrac\Deptrac\Supportive\Console\Command\AnalyseCommand;
use Deptrac\Deptrac\Supportive\Console\Command\AnalyseRunner;
use Deptrac\Deptrac\Supportive\Console\Command\ChangedFilesCommand;
use Deptrac\Deptrac\Supportive\Console\Command\ChangedFilesRunner;
use Deptrac\Deptrac\Supportive\Console\Command\DebugDependenciesCommand;
use Deptrac\Deptrac\Supportive\Console\Command\DebugDependenciesRunner;
use Deptrac\Deptrac\Supportive\Console\Command\DebugLayerCommand;
use Deptrac\Deptrac\Supportive\Console\Command\DebugLayerRunner;
use Deptrac\Deptrac\Supportive\Console\Command\DebugTokenCommand;
use Deptrac\Deptrac\Supportive\Console\Command\DebugTokenRunner;
use Deptrac\Deptrac\Supportive\Console\Command\DebugUnassignedCommand;
use Deptrac\Deptrac\Supportive\Console\Command\DebugUnassignedRunner;
use Deptrac\Deptrac\Supportive\Console\Command\DebugUnusedCommand;
use Deptrac\Deptrac\Supportive\Console\Command\DebugUnusedRunner;
use Deptrac\Deptrac\Supportive\Console\Command\InitCommand;
use Deptrac\Deptrac\Supportive\File\Dumper;
use Deptrac\Deptrac\Supportive\File\YmlFileLoader;
use Deptrac\Deptrac\Supportive\OutputFormatter\BaselineOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\CodeclimateOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\Configuration\FormatterConfiguration;
use Deptrac\Deptrac\Supportive\OutputFormatter\ConsoleOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\FormatterProvider;
use Deptrac\Deptrac\Supportive\OutputFormatter\GithubActionsOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputDisplayFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputDotFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputHtmlFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\GraphVizOutputImageFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\JsonOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\JUnitOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\MermaidJSOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\TableOutputFormatter;
use Deptrac\Deptrac\Supportive\OutputFormatter\XMLOutputFormatter;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\EventDispatcher;
use function DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;
return static function (ContainerConfigurator $container) : void {
    $services = $container->services();
    $services->defaults()->autowire();
    /*
     * Utilities
     */
    $services->set(EventDispatcher::class);
    $services->alias(EventDispatcherInterface::class, EventDispatcher::class);
    $services->alias(Symfony\Component\EventDispatcher\EventDispatcherInterface::class, EventDispatcher::class);
    $services->alias('event_dispatcher', EventDispatcher::class);
    $services->set(FileInputCollector::class)->args(['$paths' => param('paths'), '$excludedFilePatterns' => param('exclude_files'), '$basePath' => param('projectDirectory')]);
    $services->alias(InputCollectorInterface::class, FileInputCollector::class);
    $services->set(YmlFileLoader::class);
    $services->set(Dumper::class)->args(['$templateFile' => __DIR__ . '/deptrac_template.yaml']);
    /*
     * AST
     */
    $services->set(AstLoader::class);
    $services->set(ParserFactory::class);
    $services->set(Lexer::class);
    $services->set(Parser::class)->factory([service(ParserFactory::class), 'create'])->args(['$kind' => ParserFactory::PREFER_PHP7]);
    $services->set(AstFileReferenceInMemoryCache::class);
    $services->alias(AstFileReferenceCacheInterface::class, AstFileReferenceInMemoryCache::class);
    $services->set(NikicPhpParser::class)->args(['$extractors' => tagged_iterator('reference_extractors')]);
    $services->alias(ParserInterface::class, NikicPhpParser::class);
    $services->set(TypeResolver::class);
    $services->set(AnnotationReferenceExtractor::class)->tag('reference_extractors');
    $services->set(AnonymousClassExtractor::class)->tag('reference_extractors');
    $services->set(ClassConstantExtractor::class)->tag('reference_extractors');
    $services->set(FunctionLikeExtractor::class)->tag('reference_extractors');
    $services->set(PropertyExtractor::class)->tag('reference_extractors');
    $services->set(KeywordExtractor::class)->tag('reference_extractors');
    $services->set(StaticExtractor::class)->tag('reference_extractors');
    $services->set(FunctionLikeExtractor::class)->tag('reference_extractors');
    $services->set(VariableExtractor::class)->tag('reference_extractors');
    $services->set(FunctionCallResolver::class)->tag('reference_extractors');
    /*
     * Dependency
     */
    $services->set(DependencyResolver::class)->args(['$config' => param('analyser'), '$emitterLocator' => tagged_locator('dependency_emitter', 'key')]);
    $services->set(TokenResolver::class);
    $services->set(InheritanceFlattener::class);
    $services->set(ClassDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::CLASS_TOKEN->value]);
    $services->set(ClassSuperglobalDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::CLASS_SUPERGLOBAL_TOKEN->value]);
    $services->set(FileDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::FILE_TOKEN->value]);
    $services->set(FunctionDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::FUNCTION_TOKEN->value]);
    $services->set(FunctionCallDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::FUNCTION_CALL->value]);
    $services->set(FunctionSuperglobalDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::FUNCTION_SUPERGLOBAL_TOKEN->value]);
    $services->set(UsesDependencyEmitter::class)->tag('dependency_emitter', ['key' => EmitterType::USE_TOKEN->value]);
    /*
     * Layer
     */
    $services->set(LayerResolver::class)->args(['$layersConfig' => param('layers')]);
    $services->alias(LayerResolverInterface::class, LayerResolver::class);
    $services->set(CollectorProvider::class)->args(['$collectorLocator' => tagged_locator('collector', 'type')]);
    $services->set(CollectorResolver::class);
    $services->alias(CollectorResolverInterface::class, CollectorResolver::class);
    $services->set(AttributeCollector::class)->tag('collector', ['type' => CollectorType::TYPE_ATTRIBUTE->value]);
    $services->set(BoolCollector::class)->tag('collector', ['type' => CollectorType::TYPE_BOOL->value]);
    $services->set(ClassCollector::class)->tag('collector', ['type' => CollectorType::TYPE_CLASS->value]);
    $services->set(ClassLikeCollector::class)->tag('collector', ['type' => CollectorType::TYPE_CLASSLIKE->value]);
    $services->set(ClassNameRegexCollector::class)->tag('collector', ['type' => CollectorType::TYPE_CLASS_NAME_REGEX->value]);
    $services->set(TagValueRegexCollector::class)->tag('collector', ['type' => CollectorType::TYPE_TAG_VALUE_REGEX->value]);
    $services->set(DirectoryCollector::class)->tag('collector', ['type' => CollectorType::TYPE_DIRECTORY->value]);
    $services->set(ExtendsCollector::class)->tag('collector', ['type' => CollectorType::TYPE_EXTENDS->value]);
    $services->set(FunctionNameCollector::class)->tag('collector', ['type' => CollectorType::TYPE_FUNCTION_NAME->value]);
    $services->set(GlobCollector::class)->args(['$basePath' => param('projectDirectory')])->tag('collector', ['type' => CollectorType::TYPE_GLOB->value]);
    $services->set(ImplementsCollector::class)->tag('collector', ['type' => CollectorType::TYPE_IMPLEMENTS->value]);
    $services->set(InheritanceLevelCollector::class)->tag('collector', ['type' => CollectorType::TYPE_INHERITANCE->value]);
    $services->set(InterfaceCollector::class)->tag('collector', ['type' => CollectorType::TYPE_INTERFACE->value]);
    $services->set(InheritsCollector::class)->tag('collector', ['type' => CollectorType::TYPE_INHERITS->value]);
    $services->set(LayerCollector::class)->tag('collector', ['type' => CollectorType::TYPE_LAYER->value]);
    $services->set(MethodCollector::class)->tag('collector', ['type' => CollectorType::TYPE_METHOD->value]);
    $services->set(SuperglobalCollector::class)->tag('collector', ['type' => CollectorType::TYPE_SUPERGLOBAL->value]);
    $services->set(TraitCollector::class)->tag('collector', ['type' => CollectorType::TYPE_TRAIT->value]);
    $services->set(UsesCollector::class)->tag('collector', ['type' => CollectorType::TYPE_USES->value]);
    $services->set(PhpInternalCollector::class)->tag('collector', ['type' => CollectorType::TYPE_PHP_INTERNAL->value]);
    $services->set(ComposerCollector::class)->tag('collector', ['type' => CollectorType::TYPE_COMPOSER->value]);
    /*
     * Analyser
     */
    $services->set(AstMapExtractor::class);
    $services->set(UncoveredDependentHandler::class)->args(['$ignoreUncoveredInternalClasses' => param('ignore_uncovered_internal_classes')])->tag('kernel.event_subscriber');
    $services->set(MatchingLayersHandler::class)->tag('kernel.event_subscriber');
    $services->set(LayerProvider::class)->args(['$allowedLayers' => param('ruleset')]);
    $services->set(AllowDependencyHandler::class)->tag('kernel.event_subscriber');
    $services->set(DependsOnDisallowedLayer::class)->tag('kernel.event_subscriber');
    $services->set(DependsOnPrivateLayer::class)->tag('kernel.event_subscriber');
    $services->set(DependsOnInternalToken::class)->tag('kernel.event_subscriber')->args(['$config' => param('analyser')]);
    $services->set(UnmatchedSkippedViolations::class)->tag('kernel.event_subscriber');
    $services->set(EventHelper::class)->args(['$skippedViolations' => param('skip_violations')]);
    $services->set(DependencyLayersAnalyser::class);
    $services->set(TokenInLayerAnalyser::class)->args(['$config' => param('analyser')]);
    $services->set(LayerForTokenAnalyser::class);
    $services->set(UnassignedTokenAnalyser::class)->args(['$config' => param('analyser')]);
    $services->set(LayerDependenciesAnalyser::class);
    $services->set(RulesetUsageAnalyser::class)->args(['$layers' => param('layers')]);
    /*
     * OutputFormatter
     */
    $services->set(FormatterConfiguration::class)->args(['$config' => param('formatters')]);
    $services->set(FormatterProvider::class)->args(['$formatterLocator' => tagged_locator('output_formatter', null, 'getName')]);
    $services->set(ConsoleOutputFormatter::class)->tag('output_formatter');
    $services->set(GithubActionsOutputFormatter::class)->tag('output_formatter');
    $services->set(JUnitOutputFormatter::class)->tag('output_formatter');
    $services->set(TableOutputFormatter::class)->tag('output_formatter');
    $services->set(XMLOutputFormatter::class)->tag('output_formatter');
    $services->set(BaselineOutputFormatter::class)->tag('output_formatter');
    $services->set(JsonOutputFormatter::class)->tag('output_formatter');
    $services->set(GraphVizOutputDisplayFormatter::class)->tag('output_formatter');
    $services->set(GraphVizOutputImageFormatter::class)->tag('output_formatter');
    $services->set(GraphVizOutputDotFormatter::class)->tag('output_formatter');
    $services->set(GraphVizOutputHtmlFormatter::class)->tag('output_formatter');
    $services->set(CodeclimateOutputFormatter::class)->tag('output_formatter');
    $services->set(MermaidJSOutputFormatter::class)->tag('output_formatter');
    /*
     * Console
     */
    $services->set(InitCommand::class)->autowire()->tag('console.command');
    $services->set(AnalyseRunner::class)->autowire();
    $services->set(AnalyseCommand::class)->autowire()->tag('console.command');
    $services->set(ChangedFilesRunner::class)->autowire();
    $services->set(ChangedFilesCommand::class)->autowire()->tag('console.command');
    $services->set(DebugLayerRunner::class)->autowire()->args(['$layers' => param('layers')]);
    $services->set(DebugLayerCommand::class)->autowire()->tag('console.command');
    $services->set(DebugTokenRunner::class)->autowire();
    $services->set(DebugTokenCommand::class)->autowire()->tag('console.command');
    $services->set(DebugUnassignedRunner::class)->autowire();
    $services->set(DebugUnassignedCommand::class)->autowire()->tag('console.command');
    $services->set(DebugDependenciesRunner::class)->autowire();
    $services->set(DebugDependenciesCommand::class)->autowire()->tag('console.command');
    $services->set(DebugUnusedRunner::class)->autowire();
    $services->set(DebugUnusedCommand::class)->autowire()->tag('console.command');
};
