<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\DefaultBehavior\Layer;

use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Ast\CouldNotParseFileException;
use Deptrac\Deptrac\Contract\Layer\CollectorInterface;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\Helpers\ComposerFilesParser;
use RuntimeException;

final class ComposerCollector implements CollectorInterface
{
    /**
     * @var array<string, ComposerFilesParser>
     */
    private array $parser = [];

    public function satisfy(array $config, TokenReferenceInterface $reference): bool
    {
        if (!isset($config['composerPath']) || !is_string($config['composerPath'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ComposerCollector: path to the "composer.json" file is not a string.');
        }

        if (!isset($config['composerLockPath']) || !is_string($config['composerLockPath'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ComposerCollector: path to the "composer.lock" file is not a string.');
        }

        if (!isset($config['packages']) || !is_array($config['packages'])) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration('ComposerCollector: "packages" is not an array.');
        }

        try {
            $this->parser[$config['composerLockPath']] ??= new ComposerFilesParser($config['composerLockPath']);
            $parser = $this->parser[$config['composerLockPath']];
        } catch (RuntimeException $exception) {
            throw new CouldNotParseFileException('ComposerCollector: Could not parse composer files.', 0, $exception);
        }

        try {
            $namespaces = $parser->autoloadableNamespacesForRequirements($config['packages'], true);
        } catch (RuntimeException $e) {
            throw InvalidCollectorDefinitionException::invalidCollectorConfiguration(sprintf('ComposerCollector: Non-existent package defined. %s', $e->getMessage()));
        }

        $token = $reference->getToken()->toString();

        foreach ($namespaces as $namespace) {
            if (str_starts_with($token, $namespace)) {
                return true;
            }
        }

        return false;
    }
}
