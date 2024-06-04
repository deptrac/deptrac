<?php

declare (strict_types=1);
namespace Qossmic\Deptrac\Core\Ast\Parser\Cache;

use Qossmic\Deptrac\Contract\Ast\PostCreateAstMapEvent;
use Qossmic\Deptrac\Contract\Ast\PreCreateAstMapEvent;
use DEPTRAC_INTERNAL\Symfony\Component\EventDispatcher\EventSubscriberInterface;
class CacheableFileSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly \Qossmic\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceDeferredCacheInterface $deferredCache)
    {
    }
    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents() : array
    {
        return [PreCreateAstMapEvent::class => 'onPreCreateAstMapEvent', PostCreateAstMapEvent::class => 'onPostCreateAstMapEvent'];
    }
    public function onPreCreateAstMapEvent(PreCreateAstMapEvent $event) : void
    {
        $this->deferredCache->load();
    }
    public function onPostCreateAstMapEvent(PostCreateAstMapEvent $event) : void
    {
        $this->deferredCache->write();
    }
}
