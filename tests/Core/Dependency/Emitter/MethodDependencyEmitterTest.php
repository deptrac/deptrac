<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassMethodReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\TokenReferenceInterface;
use Deptrac\Deptrac\Contract\Layer\LayerResolverInterface;
use Deptrac\Deptrac\DefaultBehavior\Dependency\MethodDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class MethodDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        $emitter = new MethodDependencyEmitter($this->buildLayerResolverForMethods([]));

        self::assertSame('MethodDependencyEmitter', $emitter->getName());
    }

    public function testLayeredMethodBecomesDepender(): void
    {
        $deps = $this->getEmittedDependencies(
            new MethodDependencyEmitter($this->buildLayerResolverForMethods(['layered'])),
            __DIR__.'/Fixtures/MethodGranularity.php'
        );

        self::assertEqualsCanonicalizing(
            [
                'Foo\MethodEmitterClass::layered():7 on Foo\SomeParam',
                'Foo\MethodEmitterClass::layered():9 on Foo\MethodEmitterClass::plain()',
                'Foo\MethodEmitterClass::layered():10 on Foo\SomeClass',
                // methods without a layer of their own keep the class as depender
                'Foo\MethodEmitterClass:15 on Foo\OtherClass',
            ],
            $deps
        );
    }

    public function testUnlayeredMethodsKeepClassDepender(): void
    {
        $deps = $this->getEmittedDependencies(
            new MethodDependencyEmitter($this->buildLayerResolverForMethods([])),
            __DIR__.'/Fixtures/MethodGranularity.php'
        );

        self::assertEqualsCanonicalizing(
            [
                'Foo\MethodEmitterClass:7 on Foo\SomeParam',
                'Foo\MethodEmitterClass:9 on Foo\MethodEmitterClass::plain()',
                'Foo\MethodEmitterClass:10 on Foo\SomeClass',
                'Foo\MethodEmitterClass:15 on Foo\OtherClass',
            ],
            $deps
        );
    }

    /**
     * @param list<string> $layeredMethodNames
     */
    private function buildLayerResolverForMethods(array $layeredMethodNames): LayerResolverInterface
    {
        return new class($layeredMethodNames) implements LayerResolverInterface {
            public function __construct(private readonly array $layeredMethodNames) {}

            public function getLayersForReference(TokenReferenceInterface $reference): array
            {
                if ($reference instanceof ClassMethodReference
                    && in_array($reference->getToken()->getMethodName(), $this->layeredMethodNames, true)
                ) {
                    return ['App' => true];
                }

                return [];
            }

            public function isReferenceInLayer(string $layer, TokenReferenceInterface $reference): bool
            {
                return [] !== $this->getLayersForReference($reference);
            }

            public function has(string $layer): bool
            {
                return false;
            }
        };
    }
}
