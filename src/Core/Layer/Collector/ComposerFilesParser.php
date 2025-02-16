<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use JsonException;
use RuntimeException;
class ComposerFilesParser
{
    /**
     * @var array{
     *     packages?: array<string, array{
     *          name: string,
     *          autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *          autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *     }>,
     *     packages-dev?: array<string, array{
     *          name: string,
     *          autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *          autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *     }>,
     * }
     */
    private array $lockFile;
    /**
     * @var array<string, array{
     *     autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *     autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     * }>
     */
    private array $lockedPackages;
    /**
     * @throws RuntimeException
     */
    public function __construct(string $lockFile)
    {
        $contents = \file_get_contents($lockFile);
        if (\false === $contents) {
            throw new RuntimeException('Could not load composer.lock file');
        }
        try {
            /**
             * @var array{
             *     packages?: array<string, array{
             *          name: string,
             *          autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
             *          autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
             *     }>,
             *     packages-dev?: array<string, array{
             *          name: string,
             *          autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
             *          autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
             *     }>,
             * } $jsonDecode
             */
            $jsonDecode = \json_decode($contents, \true, 512, \JSON_THROW_ON_ERROR);
            $this->lockFile = $jsonDecode;
        } catch (JsonException $exception) {
            throw new RuntimeException('Could not parse composer.lock file', 0, $exception);
        }
        $this->lockedPackages = $this->getPackagesFromLockFile();
    }
    /**
     * Resolves an array of package names to an array of namespaces declared by those packages.
     *
     * @param string[] $requirements
     *
     * @return string[]
     *
     * @throws RuntimeException
     */
    public function autoloadableNamespacesForRequirements(array $requirements, bool $includeDev) : array
    {
        $namespaces = [[]];
        foreach ($requirements as $package) {
            if (!\array_key_exists($package, $this->lockedPackages)) {
                throw new RuntimeException(\sprintf('Could not find a "%s" package', $package));
            }
            $namespaces[] = $this->extractNamespaces($this->lockedPackages[$package], $includeDev);
        }
        return \array_merge(...$namespaces);
    }
    /**
     * @return array<string, array{
     *     autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *     autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     * }>
     */
    private function getPackagesFromLockFile() : array
    {
        $lockedPackages = [];
        foreach ($this->lockFile['packages'] ?? [] as $package) {
            $lockedPackages[$package['name']] = $package;
        }
        foreach ($this->lockFile['packages-dev'] ?? [] as $package) {
            $lockedPackages[$package['name']] = $package;
        }
        return $lockedPackages;
    }
    /**
     * @param array{
     *     autoload?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     *     autoload-dev?: array{'psr-0'?: array<string, string>, 'psr-4'?: array<string, string>},
     * } $package
     *
     * @return string[]
     */
    private function extractNamespaces(array $package, bool $includeDev) : array
    {
        $namespaces = [];
        foreach (\array_keys($package['autoload']['psr-0'] ?? []) as $namespace) {
            $namespaces[] = $namespace;
        }
        foreach (\array_keys($package['autoload']['psr-4'] ?? []) as $namespace) {
            $namespaces[] = $namespace;
        }
        if ($includeDev) {
            foreach (\array_keys($package['autoload-dev']['psr-0'] ?? []) as $namespace) {
                $namespaces[] = $namespace;
            }
            foreach (\array_keys($package['autoload-dev']['psr-4'] ?? []) as $namespace) {
                $namespaces[] = $namespace;
            }
        }
        return $namespaces;
    }
}
