<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\DependencyInjection;

use Closure;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use ReflectionFunction;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;
use function is_callable;
use function is_object;

/**
 * Custom PHP file loader that provides DeptracConfig to configuration closures.
 * This maintains backward compatibility after Symfony's ConfigBuilderGenerator was deprecated.
 */
final class DeptracPhpConfigLoader extends PhpFileLoader
{
    public function load(mixed $resource, ?string $type = null): mixed
    {
        assert(is_string($resource), 'Argument "$resource" must be of type string!');

        $path = $this->locator->locate($resource);
        $this->setCurrentDir(dirname($path));
        $this->container->fileExists($path);

        // Load the PHP file
        $load = Closure::bind(static function ($path) {
            return include $path;
        }, null, null);

        $result = $load($path);

        // If it's a callable expecting DeptracConfig, call it with our config instance
        if (is_object($result) && is_callable($result)) {
            $reflectionFunction = new ReflectionFunction($result);
            $parameters = $reflectionFunction->getParameters();

            // Check if first parameter is DeptracConfig
            if (isset($parameters[0])) {
                $firstParamType = $parameters[0]->getType();
                if ($firstParamType instanceof ReflectionNamedType
                    && DeptracConfig::class === $firstParamType->getName()
                ) {
                    // Call with DeptracConfig instance and ContainerConfigurator
                    $config = new DeptracConfig();
                    $instanceof = [];
                    $containerConfigurator = new ContainerConfigurator(
                        $this->container,
                        $this,
                        $instanceof,
                        $path,
                        $resource,
                        $this->env ?? null
                    );

                    $result($config, $containerConfigurator);

                    // Load the configuration array into the extension
                    $configArray = $config->toArray();
                    $this->container->loadFromExtension($config->getExtensionAlias(), $configArray);

                    return null;
                }
            }
        }

        // Otherwise use parent behavior
        return parent::load($resource, $type);
    }
}
