<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Ast\Parser\Helpers;

use PHPStan\Analyser\ScopeFactory;
use PHPStan\DependencyInjection\Container;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\FileTypeMapper;
use Symfony\Component\Filesystem\Path;

class PhpStanContainerDecorator
{
    private Container $container;

    /**
     * @param list<string> $paths
     */
    public function __construct(string $projectDirectory, string $cwd, array $paths)
    {
        $factory = new ContainerFactory($cwd);
        $paths = array_map(static function (string $path) use ($projectDirectory): string {
            if (Path::isRelative($path)) {
                /** @throws void */
                return Path::makeAbsolute($path, $projectDirectory);
            }

            return $path;
        }, $paths);
        $this->container = $factory->create(sys_get_temp_dir(), [
            __DIR__.'/config/config.neon',
            __DIR__.'/config/parser.neon',
        ], $paths);
    }

    public function createReflectionProvider(): ReflectionProvider
    {
        return $this->container->getByType(ReflectionProvider::class);
    }

    public function createPHPStanParser(): Parser
    {
        $service = $this->container->getService('currentPhpVersionRichParser');
        assert($service instanceof Parser);

        return $service;
    }

    public function createScopeFactory(): ScopeFactory
    {
        return $this->container->getByType(ScopeFactory::class);
    }

    public function createFileTypeMapper(): FileTypeMapper
    {
        return $this->container->getByType(FileTypeMapper::class);
    }
}
