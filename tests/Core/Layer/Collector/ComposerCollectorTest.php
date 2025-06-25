<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Layer\Collector;

use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeType;
use Deptrac\Deptrac\Contract\Layer\InvalidCollectorDefinitionException;
use Deptrac\Deptrac\DefaultBehavior\Layer\ComposerCollector;
use PHPUnit\Framework\TestCase;

final class ComposerCollectorTest extends TestCase
{
    private ComposerCollector $sut;

    public function setUp(): void
    {
        $this->sut = new ComposerCollector();
    }

    public static function dataProviderSatisfy(): iterable
    {
        yield [
            [
                'composerPath' => __DIR__.DIRECTORY_SEPARATOR.'data/composer.json',
                'composerLockPath' => __DIR__.DIRECTORY_SEPARATOR.'data/composer.lock',
                'packages' => ['phpstan/phpdoc-parser'],
            ],
            'PHPStan\\PhpDocParser\\Ast\\Attribute',
            true,
        ];
        yield [
            [
                'composerPath' => __DIR__.DIRECTORY_SEPARATOR.'data/composer.json',
                'composerLockPath' => __DIR__.DIRECTORY_SEPARATOR.'data/composer.lock',
                'packages' => ['phpstan/phpdoc-parser'],
            ],
            'Completely\\Wrong\\Namespace\\Attribute',
            false,
        ];
    }

    /**
     * @dataProvider dataProviderSatisfy
     */
    public function testSatisfy(array $configuration, string $className, bool $expected): void
    {
        $stat = $this->sut->satisfy(
            $configuration,
            new ClassLikeReference(ClassLikeToken::fromFQCN($className), ClassLikeType::TYPE_CLASS),
        );

        self::assertSame($expected, $stat);
    }

    public function testComposerPackageDoesNotExist(): void
    {
        $this->expectException(InvalidCollectorDefinitionException::class);

        $this->sut->satisfy(
            [
                'composerPath' => __DIR__.DIRECTORY_SEPARATOR.'data/composer.json',
                'composerLockPath' => __DIR__.DIRECTORY_SEPARATOR.'data/composer.lock',
                'packages' => ['fake_package'],
            ],
            new ClassLikeReference(ClassLikeToken::fromFQCN(''), ClassLikeType::TYPE_CLASS),
        );
    }
}
