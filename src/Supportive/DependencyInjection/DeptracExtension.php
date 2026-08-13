<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Config\CollectorScope;
use Deptrac\Deptrac\Contract\Config\EmitterType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

use function getcwd;
use function in_array;
use function is_array;

/**
 * @psalm-suppress UndefinedDocblockClass
 * @psalm-suppress MixedArgument
 */
class DeptracExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $configs = $this->processConfiguration($configuration, $configs);

        $container->setParameter('paths', $configs['paths']);
        $container->setParameter('exclude_files', $configs['exclude_files']);
        $container->setParameter('layers', $configs['layers']);
        $container->setParameter('ruleset', $configs['ruleset']);
        $container->setParameter('skip_violations', $configs['skip_violations']);
        $container->setParameter('formatters', $configs['formatters'] ?? []);
        $container->setParameter('analyser', $configs['analyser']);
        $container->setParameter('ignore_uncovered_internal_classes', $configs['ignore_uncovered_internal_classes']);
        $container->setParameter('cache_file', $configs['cache_file']);
        $container->setParameter('feature_flags', $configs['feature_flags']);
        $container->setParameter('method_granularity', self::methodGranularityEnabled($configs));
    }

    /**
     * Method references are only extracted (and cached) when the config opts
     * into method-level analysis, so non-opted projects keep the smaller AST
     * and cache.
     *
     * @param array<mixed> $configs
     */
    private static function methodGranularityEnabled(array $configs): bool
    {
        $analyser = $configs['analyser'] ?? [];
        $types = is_array($analyser) ? ($analyser['types'] ?? []) : [];
        if (is_array($types) && in_array(EmitterType::METHOD_TOKEN->value, $types, true)) {
            return true;
        }

        $layers = $configs['layers'] ?? [];
        foreach (is_array($layers) ? $layers : [] as $layer) {
            $collectors = is_array($layer) ? ($layer['collectors'] ?? []) : [];
            foreach (is_array($collectors) ? $collectors : [] as $collectorConfig) {
                if (is_array($collectorConfig) && self::usesMethodScope($collectorConfig)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<mixed> $collectorConfig
     */
    private static function usesMethodScope(array $collectorConfig): bool
    {
        if (CollectorScope::TYPE_METHOD->value === ($collectorConfig['scope'] ?? null)) {
            return true;
        }

        foreach (['must', 'must_not'] as $key) {
            $childConfigs = $collectorConfig[$key] ?? [];
            foreach (is_array($childConfigs) ? $childConfigs : [] as $childConfig) {
                if (is_array($childConfig) && self::usesMethodScope($childConfig)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('projectDirectory')) {
            $container->setParameter('projectDirectory', getcwd());
        }
        if (!$container->hasParameter('paths')) {
            $container->setParameter('paths', []);
        }
        if (!$container->hasParameter('exclude_files')) {
            $container->setParameter('exclude_files', []);
        }
        if (!$container->hasParameter('layers')) {
            $container->setParameter('layers', []);
        }
        if (!$container->hasParameter('ruleset')) {
            $container->setParameter('ruleset', []);
        }
        if (!$container->hasParameter('skip_violations')) {
            $container->setParameter('skip_violations', []);
        }
        if (!$container->hasParameter('formatters')) {
            $container->setParameter('formatters', []);
        }
        if (!$container->hasParameter('analyser')) {
            $container->setParameter('analyser', [
                'internal_tag' => null,
                'types' => [EmitterType::CLASS_TOKEN->value, EmitterType::FUNCTION_TOKEN->value],
            ]);
        }
        if (!$container->hasParameter('ignore_uncovered_internal_classes')) {
            $container->setParameter('ignore_uncovered_internal_classes', true);
        }
        if (!$container->hasParameter('cache_file')) {
            $container->setParameter('cache_file', '.deptrac.cache');
        }
        if (!$container->hasParameter('feature_flags')) {
            $container->setParameter('feature_flags', ['phpstan_parser' => false]);
        }
        if (!$container->hasParameter('method_granularity')) {
            $container->setParameter('method_granularity', false);
        }
    }
}
