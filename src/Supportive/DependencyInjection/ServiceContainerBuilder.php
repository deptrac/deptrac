<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Supportive\DependencyInjection;

use Exception;
use Deptrac\Deptrac\Supportive\DependencyInjection\Exception\CacheFileException;
use Deptrac\Deptrac\Supportive\DependencyInjection\Exception\CannotLoadConfiguration;
use SplFileInfo;
use DEPTRAC_INTERNAL\Symfony\Component\Config\Builder\ConfigBuilderGenerator;
use DEPTRAC_INTERNAL\Symfony\Component\Config\FileLocator;
use DEPTRAC_INTERNAL\Symfony\Component\Config\Loader\DelegatingLoader;
use DEPTRAC_INTERNAL\Symfony\Component\Config\Loader\LoaderResolver;
use DEPTRAC_INTERNAL\Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\ContainerBuilder;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use DEPTRAC_INTERNAL\Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
final class ServiceContainerBuilder
{
    private ?SplFileInfo $configFile = null;
    private ?SplFileInfo $cacheFile = null;
    private const DEPTRAC_INTERNAL_CONFIG_PATH = __DIR__ . '/../../../config';
    public function __construct(private readonly string $workingDirectory)
    {
    }
    public function withConfig(?string $configFile) : self
    {
        if (null === $configFile) {
            return $this;
        }
        $builder = clone $this;
        if (Path::isRelative($configFile)) {
            /** @throws void */
            $configFile = Path::makeAbsolute($configFile, $this->workingDirectory);
        }
        $builder->configFile = new SplFileInfo($configFile);
        return $builder;
    }
    private function withCache(string $cacheFile) : void
    {
        if (Path::isRelative($cacheFile)) {
            /** @throws void */
            $cacheFile = Path::makeAbsolute($cacheFile, $this->workingDirectory);
        }
        $this->cacheFile = new SplFileInfo($cacheFile);
    }
    private function clearCache(string $cacheFile) : void
    {
        if (Path::isRelative($cacheFile)) {
            /** @throws void */
            $cacheFile = Path::makeAbsolute($cacheFile, $this->workingDirectory);
        }
        \unlink($cacheFile);
    }
    /**
     * @throws CacheFileException
     * @throws CannotLoadConfiguration
     */
    public function build(string|false|null $cacheOverride, bool $clearCache) : ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('currentWorkingDirectory', $this->workingDirectory);
        self::registerCompilerPasses($container);
        $container->registerExtension(new \Deptrac\Deptrac\Supportive\DependencyInjection\DeptracExtension());
        $container->setParameter('projectDirectory', $this->workingDirectory);
        self::loadServices($container);
        if (null !== $this->configFile) {
            self::loadConfiguration($container, $this->configFile);
        }
        /** @var ?string $cacheFileFromConfig */
        $cacheFileFromConfig = $container->getExtensionConfig('deptrac')[0]['cache_file'] ?? null;
        // if there is any
        $cache = $cacheOverride ?? $cacheFileFromConfig;
        // override if there is a no-cache or path to file
        $cache = $cache ?? '.deptrac.cache';
        // override if there is no file specified and needs one
        if (\false !== $cache) {
            if ($clearCache) {
                $this->clearCache($cache);
            }
            $this->withCache($cache);
        }
        if (null !== $this->cacheFile) {
            self::loadCache($container, $this->cacheFile);
        }
        $container->compile(\true);
        return $container;
    }
    private static function registerCompilerPasses(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->addCompilerPass(new RegisterListenersPass());
    }
    /**
     * @throws CannotLoadConfiguration
     */
    private static function loadServices(ContainerBuilder $container) : void
    {
        $loader = new PhpFileLoader($container, new FileLocator([self::DEPTRAC_INTERNAL_CONFIG_PATH]));
        try {
            $loader->load('services.php');
        } catch (Exception $exception) {
            throw CannotLoadConfiguration::fromServices('services.php', $exception->getMessage());
        }
    }
    /**
     * @throws CacheFileException
     * @throws CannotLoadConfiguration
     */
    private static function loadCache(ContainerBuilder $container, SplFileInfo $cacheFile) : void
    {
        $loader = new PhpFileLoader($container, new FileLocator([self::DEPTRAC_INTERNAL_CONFIG_PATH]));
        if (!\file_exists($cacheFile->getPathname())) {
            $dirname = $cacheFile->getPath() ?: '.';
            if (!\is_dir($dirname) && \mkdir($dirname . '/', 0777, \true) && !\is_dir($dirname)) {
                throw CacheFileException::notWritable($cacheFile);
            }
            if (!\touch($cacheFile->getPathname()) && !\is_writable($cacheFile->getPathname())) {
                throw CacheFileException::notWritable($cacheFile);
            }
        }
        $container->setParameter('cache_file', $cacheFile->getPathname());
        try {
            $loader->load('cache.php');
        } catch (Exception $exception) {
            throw CannotLoadConfiguration::fromCache('cache.php', $exception->getMessage());
        }
    }
    /**
     * @throws CannotLoadConfiguration
     */
    private static function loadConfiguration(ContainerBuilder $container, SplFileInfo $configFile) : void
    {
        $configPathInfo = $configFile->getPathInfo();
        /** @phpstan-ignore-next-line false positive */
        if (null === $configPathInfo) {
            throw CannotLoadConfiguration::fromConfig($configFile->getFilename(), 'Unable to load config: Invalid or missing path.');
        }
        $container->setParameter('projectDirectory', $configPathInfo->getPathname());
        $loader = new DelegatingLoader(new LoaderResolver([new YamlFileLoader($container, new FileLocator([$configPathInfo->getPathname()])), new PhpFileLoader($container, new FileLocator([$configPathInfo->getPathname()]), generator: new ConfigBuilderGenerator('.'))]));
        try {
            $loader->load($configFile->getFilename());
        } catch (Exception $exception) {
            throw CannotLoadConfiguration::fromConfig($configFile->getFilename(), $exception->getMessage());
        }
    }
}
