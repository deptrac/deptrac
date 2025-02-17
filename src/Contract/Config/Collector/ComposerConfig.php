<?php

namespace Deptrac\Deptrac\Contract\Config\Collector;

use Deptrac\Deptrac\Contract\Config\CollectorConfig;
use Deptrac\Deptrac\Contract\Config\CollectorType;
final class ComposerConfig extends CollectorConfig
{
    protected CollectorType $collectorType = CollectorType::TYPE_COMPOSER;
    /** @var list<string> */
    private array $packages = [];
    private function __construct(private readonly string $composerPath, private readonly string $composerLockPath)
    {
    }
    /**
     * @param list<string> $packages
     */
    public static function create(string $composerPath = 'composer.json', string $composerLockPath = 'composer.lock', array $packages = []) : self
    {
        $result = new self($composerPath, $composerLockPath);
        foreach ($packages as $package) {
            $result->addPackage($package);
        }
        return $result;
    }
    public function addPackage(string $package) : self
    {
        $this->packages[] = $package;
        return $this;
    }
    /** @return array{
     *     composerPath: string,
     *     composerLockPath: string,
     *     packages: list<string>,
     *     private: bool,
     *     type: string}
     */
    public function toArray() : array
    {
        return ['composerPath' => $this->composerPath, 'composerLockPath' => $this->composerLockPath, 'packages' => $this->packages, 'private' => $this->private, 'type' => $this->collectorType->value];
    }
}
