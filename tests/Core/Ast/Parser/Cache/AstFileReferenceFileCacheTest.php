<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Cache;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\CustomToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenInterface;
use Deptrac\Deptrac\Core\Ast\Parser\Cache\AstFileReferenceFileCache;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Deptrac\Deptrac\Core\Ast\Parser\Cache\Fixtures\CustomCachedToken;

final class AstFileReferenceFileCacheTest extends TestCase
{
    private string $cacheFile;

    protected function setUp(): void
    {
        $this->cacheFile = (string) tempnam(sys_get_temp_dir(), 'deptrac_cache_test');
    }

    protected function tearDown(): void
    {
        @unlink($this->cacheFile);
    }

    public function testCustomTokensSurviveRoundTrip(): void
    {
        $token = new CustomToken(
            'acme/my-extension.method',
            ['class' => 'App\Fixture', 'method' => 'bar'],
            'App\Fixture::bar()',
        );

        $writer = new AstFileReferenceFileCache($this->cacheFile, '1.0.0', 'ext-salt');
        $writer->set($this->fileReferenceWithToken($token));
        $writer->write();

        $reader = new AstFileReferenceFileCache($this->cacheFile, '1.0.0', 'ext-salt');
        $reference = $reader->get(__FILE__);

        self::assertNotNull($reference);
        $cachedToken = $reference->classLikeReferences[0]->dependencies[0]->token;
        self::assertInstanceOf(CustomToken::class, $cachedToken);
        self::assertSame('acme/my-extension.method', $cachedToken->type);
        self::assertSame(['class' => 'App\Fixture', 'method' => 'bar'], $cachedToken->payload);
        self::assertSame('App\Fixture::bar()', $cachedToken->toString());
    }

    public function testEntriesWithExtensionDefinedClassesAreDiscarded(): void
    {
        $writer = new AstFileReferenceFileCache($this->cacheFile, '1.0.0');
        $writer->set($this->fileReferenceWithToken(new CustomCachedToken('custom-token')));
        $writer->write();

        $reader = new AstFileReferenceFileCache($this->cacheFile, '1.0.0');

        self::assertNull($reader->get(__FILE__));
    }

    public function testEntriesWithObjectsInCustomTokenPayloadsAreDiscarded(): void
    {
        // a CustomToken cannot be constructed with objects in its payload, so
        // forge one the way a tampered cache file could deliver it
        $token = (new ReflectionClass(CustomToken::class))->newInstanceWithoutConstructor();
        (function (): void {
            /** @var CustomToken $this */
            $this->type = 'acme.forged';
            $this->payload = ['occurrence' => new FileOccurrence(__FILE__, 1)];
            $this->display = 'forged';
        })->call($token);

        $writer = new AstFileReferenceFileCache($this->cacheFile, '1.0.0');
        $writer->set($this->fileReferenceWithToken($token));
        $writer->write();

        $reader = new AstFileReferenceFileCache($this->cacheFile, '1.0.0');

        self::assertNull($reader->get(__FILE__));
    }

    public function testVersionSaltInvalidatesCache(): void
    {
        $writer = new AstFileReferenceFileCache($this->cacheFile, '1.0.0');
        $writer->set($this->fileReferenceWithToken(new CustomToken('acme.token', [], 'token')));
        $writer->write();

        $reader = new AstFileReferenceFileCache($this->cacheFile, '1.0.0', 'ext-salt');

        self::assertNull($reader->get(__FILE__));
    }

    private function fileReferenceWithToken(TokenInterface $token): FileReference
    {
        $classReference = new ClassLikeReference(
            ClassLikeToken::fromFQCN('App\Fixture'),
            null,
            [],
            [
                new DependencyToken(
                    $token,
                    new DependencyContext(new FileOccurrence(__FILE__, 1), DependencyType::USE)
                ),
            ],
        );

        return new FileReference(__FILE__, [$classReference], [], []);
    }
}
