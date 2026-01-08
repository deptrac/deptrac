<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\DependencyInjection;

use Closure;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use LogicException;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

use function dirname;
use function is_callable;
use function is_object;
use function is_string;

/**
 * Custom PHP file loader that provides DeptracConfig to configuration closures.
 * This maintains backward compatibility after Symfony's ConfigBuilderGenerator was deprecated.
 */
final class DeptracPhpConfigLoader extends PhpFileLoader
{
    public function load(mixed $resource, ?string $type = null): mixed
    {
        if (!is_string($resource)) {
            return parent::load($resource, $type);
        }

        $path = $this->locator->locate($resource);
        $this->setCurrentDir(dirname($path));
        $this->container->fileExists($path);

        $content = self::loadFileContent($path);

        if (self::isDeptracConfigClosure($content)) {
            $this->loadDeptracConfig($content, $path, $resource);

            return null;
        }

        // Otherwise use parent behavior for standard Symfony configs
        return parent::load($resource, $type);
    }

    /**
     * Creates a closure to load PHP file content in isolated scope.
     *
     * @return mixed
     */
    private static function loadFileContent(string $path)
    {
        $loader = Closure::bind(static fn ($path) => include $path, null, null);

        return $loader($path);
    }

    /**
     * @throws ReflectionException
     */
    private static function isDeptracConfigClosure(mixed $content): bool
    {
        if (!is_object($content) || !is_callable($content)) {
            return false;
        }

        $reflection = new ReflectionFunction(Closure::fromCallable($content));
        $parameters = $reflection->getParameters();

        if (!isset($parameters[0])) {
            return false;
        }

        $firstParamType = $parameters[0]->getType();

        return $firstParamType instanceof ReflectionNamedType
            && DeptracConfig::class === $firstParamType->getName();
    }

    /**
     * @throws LogicException
     */
    private function loadDeptracConfig(mixed $loader, string $path, string $resource): void
    {
        if (!is_callable($loader)) {
            return;
        }

        $config = new DeptracConfig();
        $instanceof = [];
        $containerConfigurator = new ContainerConfigurator(
            $this->container,
            $this,
            $instanceof,
            $path,
            $resource,
            $this->env
        );

        $loader($config, $containerConfigurator);

        /** @var array<string, mixed> $configArray */
        $configArray = $config->toArray();
        $this->container->loadFromExtension(
            $config->getExtensionAlias(),
            $configArray
        );
    }
}
