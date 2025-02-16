<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Contract\Config;

use Deptrac\Deptrac\Contract\Config\Formatter\FormatterConfigInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Config\Builder\ConfigBuilderInterface;
use DEPTRAC_INTERNAL\Symfony\Component\Yaml\Exception\ParseException;
use DEPTRAC_INTERNAL\Symfony\Component\Yaml\Yaml;
final class DeptracConfig implements ConfigBuilderInterface
{
    private bool $ignoreUncoveredInternalClasses = \true;
    /** @var array<string> */
    private array $paths = [];
    /** @var array<Layer> */
    private array $layers = [];
    /** @var array<FormatterConfigInterface> */
    private array $formatters = [];
    /** @var array<Ruleset> */
    private array $rulesets = [];
    private ?\Deptrac\Deptrac\Contract\Config\AnalyserConfig $analyser = null;
    /** @var array<string, array<string>> */
    private array $skipViolations = [];
    /** @var array<string> */
    private array $excludeFiles = [];
    private ?string $cacheFile = null;
    /**
     * @deprecated use analyser(AnalyserConfig::create()) instead
     */
    public function analysers(\Deptrac\Deptrac\Contract\Config\EmitterType ...$types) : self
    {
        return $this->analyser(\Deptrac\Deptrac\Contract\Config\AnalyserConfig::create($types));
    }
    public function analyser(\Deptrac\Deptrac\Contract\Config\AnalyserConfig $analyser) : self
    {
        $this->analyser = $analyser;
        return $this;
    }
    /**
     * @throws ParseException
     */
    public function baseline(string $baseline) : self
    {
        /** @var array<string, array<string, array<string>>> $baselineAsArray */
        $baselineAsArray = Yaml::parseFile($baseline);
        /** @var array<string, array<string>> $skipViolations */
        $skipViolations = $baselineAsArray['deptrac']['skip_violations'] ?? [];
        foreach ($skipViolations as $class => $skipViolation) {
            $this->skipViolations[$class] = $skipViolation;
        }
        return $this;
    }
    public function formatters(FormatterConfigInterface ...$formatters) : self
    {
        foreach ($formatters as $formatter) {
            $this->formatters[$formatter->getName()] = $formatter;
        }
        return $this;
    }
    public function paths(string ...$paths) : self
    {
        foreach ($paths as $path) {
            $this->paths[] = $path;
        }
        return $this;
    }
    public function excludeFiles(string ...$excludeFiles) : self
    {
        foreach ($excludeFiles as $excludeFile) {
            $this->excludeFiles[] = $excludeFile;
        }
        return $this;
    }
    public function layers(\Deptrac\Deptrac\Contract\Config\Layer ...$layerConfigs) : self
    {
        foreach ($layerConfigs as $layerConfig) {
            $this->layers[$layerConfig->name] = $layerConfig;
        }
        return $this;
    }
    public function rulesets(\Deptrac\Deptrac\Contract\Config\Ruleset ...$rulesetConfigs) : self
    {
        foreach ($rulesetConfigs as $rulesetConfig) {
            $this->rulesets[$rulesetConfig->layerConfig->name] = $rulesetConfig;
        }
        return $this;
    }
    public function cacheFile(string $path) : self
    {
        $this->cacheFile = $path;
        return $this;
    }
    /** @return array<mixed> */
    public function toArray() : array
    {
        $config = [];
        if ([] !== $this->paths) {
            $config['paths'] = $this->paths;
        }
        if ($this->analyser) {
            $config['analyser'] = $this->analyser->toArray();
        }
        if ([] !== $this->formatters) {
            $config['formatters'] = \array_map(static fn(FormatterConfigInterface $formatterConfig) => $formatterConfig->toArray(), $this->formatters);
        }
        if ([] !== $this->excludeFiles) {
            $config['exclude_files'] = $this->excludeFiles;
        }
        if ([] !== $this->layers) {
            $config['layers'] = \array_map(static fn(\Deptrac\Deptrac\Contract\Config\Layer $layerConfig) => $layerConfig->toArray(), $this->layers);
        }
        if ([] !== $this->rulesets) {
            $config['ruleset'] = \array_map(static fn(\Deptrac\Deptrac\Contract\Config\Ruleset $rulesetConfig) => $rulesetConfig->toArray(), $this->rulesets);
        }
        if ([] !== $this->skipViolations) {
            $config['skip_violations'] = $this->skipViolations;
        }
        $config['ignore_uncovered_internal_classes'] = $this->ignoreUncoveredInternalClasses;
        $config['cache_file'] = $this->cacheFile;
        return $config;
    }
    public function getExtensionAlias() : string
    {
        return 'deptrac';
    }
}
