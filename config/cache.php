<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Ast\AstFileReferenceCacheInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceDeferredCacheInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceFileCache;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\CacheableFileSubscriber;
use Deptrac\Deptrac\Supportive\Console\DeptracVersion;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->defaults()
        ->public()
    ;

    $services
        ->set(AstFileReferenceFileCache::class)
        ->args(['%cache_file%', DeptracVersion::get()])
    ;

    $services->alias(AstFileReferenceDeferredCacheInterface::class, AstFileReferenceFileCache::class);
    $services->alias(AstFileReferenceCacheInterface::class, AstFileReferenceDeferredCacheInterface::class);

    $services
        ->set(CacheableFileSubscriber::class)
        ->args([service(AstFileReferenceFileCache::class)])
        ->tag('kernel.event_subscriber')
    ;
};
