<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\InputCollector;

use LogicException;
use Deptrac\Deptrac\Supportive\File\Exception\InvalidPathException;
use SplFileInfo;
use DEPTRAC_INTERNAL\Symfony\Component\Filesystem\Path;
use DEPTRAC_INTERNAL\Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use DEPTRAC_INTERNAL\Symfony\Component\Finder\Finder;
/**
 * @internal
 */
final class FileInputCollector implements \Deptrac\Deptrac\Core\InputCollector\InputCollectorInterface
{
    /**
     * @var string[]
     */
    private array $paths;
    /**
     * @param string[] $paths
     * @param string[] $excludedFilePatterns
     *
     * @throws InvalidPathException
     */
    public function __construct(array $paths, private readonly array $excludedFilePatterns, string $basePath)
    {
        $basePathInfo = new SplFileInfo($basePath);
        if (!$basePathInfo->isDir() || !$basePathInfo->isReadable()) {
            throw InvalidPathException::unreadablePath($basePathInfo);
        }
        $this->paths = [];
        foreach ($paths as $originalPath) {
            if (Path::isRelative($originalPath)) {
                /** @throws void */
                $path = Path::makeAbsolute($originalPath, $basePathInfo->getPathname());
            } else {
                $path = $originalPath;
            }
            $path = new SplFileInfo($path);
            if (!$path->isReadable()) {
                throw InvalidPathException::unreadablePath($path);
            }
            $this->paths[] = Path::canonicalize($path->getPathname());
        }
    }
    public function collect() : array
    {
        try {
            if ([] === $this->paths) {
                throw new LogicException("No 'paths' defined in the depfile.");
            }
            $finder = (new Finder())->in($this->paths)->name('*.php')->files()->followLinks()->ignoreUnreadableDirs()->ignoreVCS(\true)->notPath($this->excludedFilePatterns)->sortByName();
            $customFilterIterator = $finder->getIterator();
        } catch (LogicException|DirectoryNotFoundException $exception) {
            throw \Deptrac\Deptrac\Core\InputCollector\InputException::couldNotCollectFiles($exception);
        }
        $finder = new \Deptrac\Deptrac\Core\InputCollector\PathNameFilterIterator($customFilterIterator, [], $this->excludedFilePatterns);
        return \array_values(\array_map(static fn(SplFileInfo $fileInfo) => (string) $fileInfo->getRealPath(), \iterator_to_array($finder)));
    }
}
