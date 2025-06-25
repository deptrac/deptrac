<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser;

use Deptrac\Deptrac\Contract\Ast\PostCreateAstMapEvent;
use Deptrac\Deptrac\Contract\Ast\PreCreateAstMapEvent;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceFileCache;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\CacheableFileSubscriber;
use PHPUnit\Framework\TestCase;

final class CacheableFileSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        self::assertSame(
            [
                PreCreateAstMapEvent::class => 'onPreCreateAstMapEvent',
                PostCreateAstMapEvent::class => 'onPostCreateAstMapEvent',
            ],
            CacheableFileSubscriber::getSubscribedEvents()
        );
    }

    public function testOnPreCreateAstMapEvent(): void
    {
        $cache = $this->createMock(AstFileReferenceFileCache::class);
        $cache->expects(self::once())->method('load');

        (new CacheableFileSubscriber($cache))->onPreCreateAstMapEvent(new PreCreateAstMapEvent(1));
    }

    public function testOnPostCreateAstMapEvent(): void
    {
        $cache = $this->createMock(AstFileReferenceFileCache::class);
        $cache->expects(self::once())->method('write');

        (new CacheableFileSubscriber($cache))->onPostCreateAstMapEvent(new PostCreateAstMapEvent());
    }
}
