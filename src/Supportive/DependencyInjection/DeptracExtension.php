<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Config\EmitterType;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\ContainerBuilder;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Extension\Extension;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use function getcwd;
/**
 * @psalm-suppress UndefinedDocblockClass
 * @psalm-suppress MixedArgument
 */
class DeptracExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new \Deptrac\Deptrac\Supportive\DependencyInjection\Configuration();
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
    }
    public function prepend(ContainerBuilder $container) : void
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
            $container->setParameter('analyser', ['types' => [EmitterType::CLASS_TOKEN->value, EmitterType::FUNCTION_TOKEN->value]]);
        }
        if (!$container->hasParameter('ignore_uncovered_internal_classes')) {
            $container->setParameter('ignore_uncovered_internal_classes', \true);
        }
        if (!$container->hasParameter('cache_file')) {
            $container->setParameter('cache_file', '.deptrac.cache');
        }
    }
}
