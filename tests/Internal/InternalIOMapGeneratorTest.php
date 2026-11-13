<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Internal;

use Deptrac\Deptrac\DefaultBehavior\Layer\Helpers\InternalIOMap;
use Internal\Deptrac\Deptrac\InternalIOMapGenerator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

use function array_keys;

final class InternalIOMapGeneratorTest extends TestCase
{
    public function testFunctionsWithAResourceOrAHandleInTheirSignaturePerformIO(): void
    {
        self::assertSame(
            [
                'accepts_optional_context',
                'consumes_migrated_handle',
                'consumes_resource',
                'consumes_resource_by_reference',
                'consumes_resources_in_array',
                'declared_for_several_php_versions',
                'migrated\consumes_handle_from_the_same_namespace',
                'migrated\namespaced_function',
                'migrated_according_to_documentation',
                'migrated_parameter',
                'migrated_return_value',
                'mixed_case',
                'produces_documented_handle',
                'produces_migrated_handle',
                'produces_resource',
            ],
            $this->generator()->map()['functions'],
        );
    }

    public function testClassesThatReplacedAResourceAreHandles(): void
    {
        self::assertSame(
            [
                'documentedhandle',
                'migrated\connection',
                'migratedhandle',
            ],
            $this->generator()->map()['handles'],
        );
    }

    public function testHandlesAndClassesWithAMethodThatPerformsIOPerformIO(): void
    {
        self::assertSame(
            [
                'child',
                'documentedhandle',
                'enumimplementation',
                'extendsproducesresource',
                'grandchild',
                'implementation',
                'methodconsumeshandle',
                'methodproducesresource',
                'migrated\connection',
                'migratedhandle',
                'producesresource',
                'traitwithresource',
            ],
            $this->generator()->map()['classes'],
        );
    }

    public function testGeneratesTheSourceCodeOfTheMap(): void
    {
        $code = $this->generator()->generate();

        self::assertStringContainsString('namespace Deptrac\Deptrac\DefaultBehavior\Layer\Helpers;', $code);
        self::assertStringContainsString('final class InternalIOMap', $code);
        self::assertStringContainsString('generated from jetbrains/phpstorm-stubs 1.0,', $code);
        self::assertStringContainsString("    public const FUNCTIONS = [\n        'accepts_optional_context' => true,\n", $code);
        self::assertStringContainsString("        'migrated\\\\namespaced_function' => true,\n", $code);
        self::assertStringContainsString("    public const CLASSES = [\n        'child' => true,\n", $code);
    }

    public function testGeneratesEmptyMapWithoutStubs(): void
    {
        $generator = new InternalIOMapGenerator(__DIR__.'/Fixtures/stubs', [], '1.0');

        self::assertSame(['functions' => [], 'classes' => [], 'handles' => []], $generator->map());
        self::assertStringContainsString('    public const FUNCTIONS = [];', $generator->generate());
    }

    public function testStubFileHasToBeReadable(): void
    {
        $generator = new InternalIOMapGenerator(__DIR__.'/Fixtures/stubs', ['does-not-exist.php'], '1.0');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does-not-exist.php" cannot be read.');

        $generator->map();
    }

    public function testMapThatShipsWithDeptracIsUpToDate(): void
    {
        $file = (new ReflectionClass(InternalIOMap::class))->getFileName();
        $generator = InternalIOMapGenerator::forInstalledStubs();

        self::assertIsString($file);
        self::assertStringEqualsFile(
            $file,
            $generator->generate(),
            'The map of PHP-internal functions and classes that perform I/O is outdated. Run "make internal-io-map" to regenerate it.',
        );

        $map = $generator->map();

        self::assertSame($map['functions'], array_keys(InternalIOMap::FUNCTIONS));
        self::assertSame($map['classes'], array_keys(InternalIOMap::CLASSES));

        self::assertContains('curlhandle', $map['handles']);
        self::assertContains('gdimage', $map['handles']);
        self::assertContains('socket', $map['handles']);
        self::assertContains('ftp\\connection', $map['handles']);
        self::assertNotContains('gmp', $map['handles']);
    }

    private function generator(): InternalIOMapGenerator
    {
        return new InternalIOMapGenerator(
            __DIR__.'/Fixtures/stubs',
            ['classes.php', 'functions.php', 'migrated/migrated.php'],
            '1.0',
        );
    }
}
