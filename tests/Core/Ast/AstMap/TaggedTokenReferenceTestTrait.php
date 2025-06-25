<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\AstMap;

use Deptrac\Deptrac\Contract\Ast\AstMap\TaggedTokenReferenceInterface;

trait TaggedTokenReferenceTestTrait
{
    abstract private function newWithTags(array $tags): TaggedTokenReferenceInterface;

    public function testTags(): void
    {
        $tags = [
            '@foo' => ['foo1'],
            '@bar' => ['bar1', 'bar2'],
        ];

        $ref = $this->newWithTags($tags);

        self::assertTrue($ref->hasTag('@foo'), 'has @foo');
        self::assertTrue($ref->hasTag('@bar'), 'has @bar');
        self::assertFalse($ref->hasTag('foo'), 'has foo');
        self::assertFalse($ref->hasTag('@xyzzy'), 'has @xyzzy');

        self::assertSame(['foo1'], $ref->getTagLines('@foo'), 'get @foo lines');
        self::assertSame(['bar1', 'bar2'], $ref->getTagLines('@bar'), 'get @bar lines');
        self::assertnull($ref->getTagLines('foo'), 'get foo lines');
        self::assertnull($ref->getTagLines('@xyzzy'), 'get @xyzzy lines');
    }
}
