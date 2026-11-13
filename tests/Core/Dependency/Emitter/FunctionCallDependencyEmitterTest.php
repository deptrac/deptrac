<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency\Emitter;

use Deptrac\Deptrac\Contract\Ast\AstMap\FileToken;
use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionCallDependencyEmitter;
use PHPUnit\Framework\TestCase;

final class FunctionCallDependencyEmitterTest extends TestCase
{
    use EmitterTrait;

    public function testGetName(): void
    {
        self::assertSame('FunctionCallDependencyEmitter', (new FunctionCallDependencyEmitter())->getName());
    }

    public function testApplyDependencies(): void
    {
        $deps = $this->getEmittedDependencies(
            new FunctionCallDependencyEmitter(),
            __DIR__.'/Fixtures/Bar.php'
        );

        self::assertEqualsCanonicalizing(
            [
                'Foo\test():15 on assert()',
                'Foo\testAnonymousClass():51 on assert()',
                'Foo\testAnonymousClass():86 on Foo\test()',
            ],
            $deps
        );
    }

    public function testEmitsCallsToFunctionsDeclaredInAnalysedCodeAndToInternalFunctions(): void
    {
        $file = __DIR__.'/Fixtures/FunctionCalls.php';

        $deps = $this->getEmittedDependencies(new FunctionCallDependencyEmitter(), $file);

        self::assertEqualsCanonicalizing(
            [
                'FunctionCalls\Caller:32 on file_get_contents()',
                // Unqualified call that falls back to the internal function
                'FunctionCalls\caller():13 on fopen()',
                'FunctionCalls\caller():14 on fclose()',
                // Function names are case-insensitive
                'FunctionCalls\caller():15 on FWRITE()',
                // Unqualified call to a function that is declared in the namespace: no fallback
                'FunctionCalls\caller():16 on FunctionCalls\strlen()',
                'FunctionCalls\caller():17 on strlen()',
                // Unqualified call that falls back to the global function declared in analysed code
                'FunctionCalls\caller():18 on globalHelper()',
                // Internal functions can be namespaced, too
                'FunctionCalls\caller():19 on Brotli\compress_init()',
                // Two global functions on one line, the name of one ends with the name of the other
                'FunctionCalls\caller():25 on acos()',
                'FunctionCalls\caller():25 on cos()',
                // Code outside of classes and functions depends on behalf of its file
                (new FileToken($file))->toString().':36 on readfile()',
            ],
            $deps
        );
    }

    public function testDoesNotEmitCallsToUnknownFunctions(): void
    {
        $deps = $this->getEmittedDependencies(
            new FunctionCallDependencyEmitter(),
            __DIR__.'/Fixtures/FunctionCalls.php'
        );

        foreach ($deps as $dep) {
            self::assertStringNotContainsString('notDeclaredAnywhere', $dep);
            // Neither a qualified nor an imported name falls back to the global function
            self::assertStringNotContainsString('Elsewhere', $dep);
            self::assertStringNotContainsString('fread', $dep);
        }
    }

    public function testFallbackIsDecidedForEachCallOnALine(): void
    {
        $deps = $this->getEmittedDependencies(
            new FunctionCallDependencyEmitter(),
            __DIR__.'/Fixtures/FunctionCallsShadowed.php'
        );

        self::assertEqualsCanonicalizing(
            [
                'FunctionCallsShadowed\caller():12 on FunctionCallsShadowed\strlen()',
                'FunctionCallsShadowed\caller():12 on strtoupper()',
            ],
            $deps
        );
    }
}
