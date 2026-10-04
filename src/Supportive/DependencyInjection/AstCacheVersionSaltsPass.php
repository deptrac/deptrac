<?php

declare(strict_types=1);

namespace Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

use function array_key_exists;
use function array_unique;
use function implode;
use function is_string;
use function sort;
use function sprintf;

/**
 * Composes the AST cache version salt from all services tagged with
 * 'ast_cache.version_salt'.
 *
 * Every extension contributes its own salt by tagging one of its registered
 * services; the salts are deduplicated and sorted, so the composed value is
 * deterministic and independent of registration order. Registering or
 * removing a salt changes the composed value and thereby invalidates caches
 * written without it.
 */
final class AstCacheVersionSaltsPass implements CompilerPassInterface
{
    public const TAG_NAME = 'ast_cache.version_salt';

    /**
     * Named constructor argument of the file-backed AST cache carrying the
     * composed salts; declared by name in cache.php so this pass does not
     * depend on the argument's position. Named arguments are resolved to
     * positions during optimization, after this pass has run.
     */
    private const SALT_ARGUMENT = '$cacheVersionSalt';

    /**
     * @throws InvalidArgumentException when a tag has no non-empty "salt" attribute
     * @throws OutOfBoundsException
     * @throws ServiceNotFoundException
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(AstFileReferenceCacheInterface::class)) {
            return;
        }

        // resolves to the file-backed cache when caching is enabled (see
        // cache.php) and to the in-memory cache otherwise; only the
        // file-backed cache persists between runs and declares the salt
        // argument
        $cache = $container->findDefinition(AstFileReferenceCacheInterface::class);

        if (!array_key_exists(self::SALT_ARGUMENT, $cache->getArguments())) {
            return;
        }

        $salts = [];

        /** @var array<string, list<array<string, mixed>>> $taggedServices */
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                $salt = $attributes['salt'] ?? null;

                if (!is_string($salt) || '' === $salt) {
                    throw new InvalidArgumentException(sprintf('The "%s" tag on service "%s" requires a non-empty "salt" attribute, e.g. { name: \'%s\', salt: \'acme/my-extension@1.0.0\' }.', self::TAG_NAME, $serviceId, self::TAG_NAME));
                }

                $salts[] = $salt;
            }
        }

        $salts = array_unique($salts);
        sort($salts);

        $cache->replaceArgument(self::SALT_ARGUMENT, implode('|', $salts));
    }
}
