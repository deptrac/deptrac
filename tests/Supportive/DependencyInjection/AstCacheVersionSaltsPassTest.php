<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceFileCache;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceInMemoryCache;
use Deptrac\Deptrac\Supportive\DependencyInjection\AstCacheVersionSaltsPass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class AstCacheVersionSaltsPassTest extends TestCase
{
    public function testComposesTaggedSaltsDeduplicatedAndSorted(): void
    {
        $container = $this->containerWithFileCache();
        $container->register('extension_b', stdClass::class)
            ->addTag(AstCacheVersionSaltsPass::TAG_NAME, ['salt' => 'vendor-b/extension@2.0'])
        ;
        $container->register('extension_a', stdClass::class)
            ->addTag(AstCacheVersionSaltsPass::TAG_NAME, ['salt' => 'vendor-a/extension@1.0'])
        ;
        // the same extension may tag several of its services with the same salt
        $container->register('extension_a_other_service', stdClass::class)
            ->addTag(AstCacheVersionSaltsPass::TAG_NAME, ['salt' => 'vendor-a/extension@1.0'])
        ;

        (new AstCacheVersionSaltsPass())->process($container);

        self::assertSame(
            'vendor-a/extension@1.0|vendor-b/extension@2.0',
            $container->getDefinition(AstFileReferenceFileCache::class)->getArgument('$cacheVersionSalt'),
        );
    }

    public function testLeavesSaltEmptyWithoutTaggedServices(): void
    {
        $container = $this->containerWithFileCache();

        (new AstCacheVersionSaltsPass())->process($container);

        self::assertSame('', $container->getDefinition(AstFileReferenceFileCache::class)->getArgument('$cacheVersionSalt'));
    }

    public function testDoesNothingWithoutRegisteredCache(): void
    {
        $container = new ContainerBuilder();
        $container->register('extension', stdClass::class)
            ->addTag(AstCacheVersionSaltsPass::TAG_NAME, ['salt' => 'vendor/extension@1.0'])
        ;

        (new AstCacheVersionSaltsPass())->process($container);

        self::assertFalse($container->has(AstFileReferenceCacheInterface::class));
    }

    public function testLeavesTheInMemoryCacheAloneWhenCachingIsDisabled(): void
    {
        $container = new ContainerBuilder();
        $container->register(AstFileReferenceInMemoryCache::class, AstFileReferenceInMemoryCache::class);
        $container->setAlias(AstFileReferenceCacheInterface::class, AstFileReferenceInMemoryCache::class);
        $container->register('extension', stdClass::class)
            ->addTag(AstCacheVersionSaltsPass::TAG_NAME, ['salt' => 'vendor/extension@1.0'])
        ;

        (new AstCacheVersionSaltsPass())->process($container);

        self::assertSame([], $container->getDefinition(AstFileReferenceInMemoryCache::class)->getArguments());
    }

    public function testRejectsTagsWithoutSaltAttribute(): void
    {
        $container = $this->containerWithFileCache();
        $container->register('extension', stdClass::class)
            ->addTag(AstCacheVersionSaltsPass::TAG_NAME)
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires a non-empty "salt" attribute');

        (new AstCacheVersionSaltsPass())->process($container);
    }

    /**
     * The cache wiring of cache.php: the concrete file cache with a named salt
     * argument, reachable through the contract alias.
     */
    private function containerWithFileCache(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->register(AstFileReferenceFileCache::class, AstFileReferenceFileCache::class)
            ->setArguments(['/tmp/cache', '1.0.0', '$cacheVersionSalt' => ''])
        ;
        $container->setAlias(AstFileReferenceCacheInterface::class, AstFileReferenceFileCache::class);

        return $container;
    }
}
