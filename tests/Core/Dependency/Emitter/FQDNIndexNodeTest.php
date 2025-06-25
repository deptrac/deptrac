<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\FQDNIndexNode;
use PHPUnit\Framework\TestCase;

use function array_slice;
use function explode;

class FQDNIndexNodeTest extends TestCase
{
    public function testIsInitializedFalse(): void
    {
        $node = new FQDNIndexNode();

        static::assertFalse($node->isFQDN());
    }

    public function testSameClassReferenceIsFQDN(): void
    {
        $node = new FQDNIndexNode();
        $reference = FQDNIndexNode::class;
        $path = explode('\\', $reference);

        $node->setNestedNode($path);

        static::assertFalse($node->isFQDN());

        $comparedNode = $node->getNestedNode($path);
        static::assertInstanceOf(FQDNIndexNode::class, $comparedNode);
        static::assertNotSame($node, $comparedNode);
        static::assertTrue($comparedNode->isFQDN());
    }

    public function testFullPathIsFQDNForSubPath(): void
    {
        $node = new FQDNIndexNode();
        $reference = FQDNIndexNode::class;
        $fullPath = explode('\\', $reference);
        $usedPath = array_slice($fullPath, 0, 2);

        $node->setNestedNode($usedPath);

        static::assertNull($node->getNestedNode($fullPath));
    }

    public function testSubPathIsNotFQDNForFullPath(): void
    {
        $node = new FQDNIndexNode();
        $reference = FQDNIndexNode::class;
        $fullPath = explode('\\', $reference);
        $usedPath = array_slice($fullPath, 0, 2);

        $node->setNestedNode($fullPath);

        static::assertFalse($node->isFQDN());

        $comparedNode = $node->getNestedNode($usedPath);
        static::assertInstanceOf(FQDNIndexNode::class, $comparedNode);
        static::assertNotSame($node, $comparedNode);
        static::assertFalse($comparedNode->isFQDN());
    }
}
