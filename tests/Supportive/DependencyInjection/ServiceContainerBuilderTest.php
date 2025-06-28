<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Supportive\DependencyInjection;

use Deptrac\Deptrac\DefaultBehavior\Ast\Parser\NikicPhpParser;
use Deptrac\Deptrac\Supportive\DependencyInjection\ServiceContainerBuilder;
use PHPUnit\Framework\TestCase;

final class ServiceContainerBuilderTest extends TestCase
{
    public function testBuildsContainerWithDefaultParameters(): void
    {
        $builder = (new ServiceContainerBuilder(__DIR__))->withConfig(__DIR__.'/config/custom.yaml');

        $container = $builder->build(null, false);

        // test service override is possible
        self::assertSame(CustomPhpParser::class, $container->getDefinition(NikicPhpParser::class)->getClass());

        self::assertTrue($container->getParameter('ignore_uncovered_internal_classes'));
        self::assertSame(
            ['internal_tag' => null, 'types' => ['class', 'function']],
            $container->getParameter('analyser')
        );
        self::assertSame(
            [],
            $container->getParameter('paths')
        );
        self::assertSame(
            [],
            $container->getParameter('exclude_files')
        );
        self::assertSame(
            [],
            $container->getParameter('layers')
        );
        self::assertSame(
            [],
            $container->getParameter('ruleset')
        );
        self::assertSame(
            [],
            $container->getParameter('skip_violations')
        );
        self::assertSame(__DIR__.'/.deptrac.cache', $container->getParameter('cache_file'));
    }
}
