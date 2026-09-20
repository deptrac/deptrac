<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Contract\Ast;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\CustomToken;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CustomTokenTest extends TestCase
{
    public function testCarriesTypePayloadAndDisplayString(): void
    {
        $token = new CustomToken(
            'acme/my-extension.route',
            ['path' => '/foo', 'methods' => ['GET', 'POST'], 'priority' => 3, 'internal' => false, 'condition' => null],
            'route:/foo',
        );

        self::assertSame('acme/my-extension.route', $token->type);
        self::assertSame('/foo', $token->payload['path']);
        self::assertSame('route:/foo', $token->toString());
    }

    public function testEqualsComparesTypeAndPayload(): void
    {
        $token = new CustomToken('acme.route', ['path' => '/foo'], 'route:/foo');

        self::assertTrue($token->equals(new CustomToken('acme.route', ['path' => '/foo'], 'route:/foo')));
        self::assertFalse($token->equals(new CustomToken('acme.command', ['path' => '/foo'], 'route:/foo')));
        self::assertFalse($token->equals(new CustomToken('acme.route', ['path' => '/bar'], 'route:/foo')));
        self::assertFalse($token->equals(ClassLikeToken::fromFQCN('App\Foo')));
    }

    public function testRejectsObjectsInPayload(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomToken('acme.route', ['token' => ClassLikeToken::fromFQCN('App\Foo')], 'route');
    }

    public function testRejectsObjectsNestedInPayloadArrays(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomToken('acme.route', ['nested' => ['deep' => [ClassLikeToken::fromFQCN('App\Foo')]]], 'route');
    }
}
