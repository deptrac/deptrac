<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class SpanFixtureAttribute
{
}

class ClassMethodSpans
{
    private const UNUSED = true;

    public function plain(): void
    {
        $fn = static function (): void {
        };
        $fn();
    }

    #[SpanFixtureAttribute]
    protected function withAttribute(): iterable
    {
        yield from [];
    }

    private static function helper(): object
    {
        return new class {
            public function anonymousMethod(): void
            {
            }
        };
    }
}

interface ClassMethodSpansInterface
{
    public function signatureOnly(): void;
}

trait ClassMethodSpansTrait
{
    public function fromTrait(): void
    {
    }
}

enum ClassMethodSpansEnum
{
    case A;

    public function label(): string
    {
        return 'a';
    }
}
