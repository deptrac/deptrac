<?php

declare (strict_types=1);
namespace Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Ast\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use RuntimeException;
final class ComposerCollector implements CollectorInterface
{
    /**
     * @var array<string, ComposerFilesParser>
     */
    private array $parser = [];
    public function satisfy(array $config, TokenReferenceInterface $reference) : bool
    {
        if (!isset($config['composerPath']) || !\is_string($config['composerPath'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ComposerCollector needs the path to the composer.json file as string.');
        }
        if (!isset($config['composerLockPath']) || !\is_string($config['composerLockPath'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ComposerCollector needs the path to the composer.lock file as string.');
        }
        if (!isset($config['packages']) || !\is_array($config['packages'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ComposerCollector needs the list of packages as string.');
        }
        try {
            $this->parser[$config['composerLockPath']] ??= new \Deptrac\Deptrac\Core\Layer\Collector\ComposerFilesParser($config['composerLockPath']);
            $parser = $this->parser[$config['composerLockPath']];
        } catch (RuntimeException $exception) {
            throw new CouldNotParseFileException('Could not parse composer files.', 0, $exception);
        }
        try {
            $namespaces = $parser->autoloadableNamespacesForRequirements($config['packages'], \true);
        } catch (RuntimeException $e) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(\sprintf('ComposerCollector has a non-existent package defined. %s', $e->getMessage()));
        }
        $token = $reference->getToken()->toString();
        foreach ($namespaces as $namespace) {
            if (\str_starts_with($token, $namespace)) {
                return \true;
            }
        }
        return \false;
    }
}
