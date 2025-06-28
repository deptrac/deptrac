<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeReference;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Contract\Config\EmitterType;
use Deptrac\Deptrac\Contract\Dependency\DependencyInterface;
use Deptrac\Deptrac\Contract\Dependency\PostEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PostFlattenEvent;
use Deptrac\Deptrac\Contract\Dependency\PreEmitEvent;
use Deptrac\Deptrac\Contract\Dependency\PreFlattenEvent;
use Deptrac\Deptrac\Core\Ast\AstMap;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
use Deptrac\Deptrac\Core\Dependency\DependencyResolver;
use Deptrac\Deptrac\Core\Dependency\InheritDependency;
use Deptrac\Deptrac\Core\Dependency\InvalidEmitterConfigurationException;
use Deptrac\Deptrac\DefaultBehavior\Dependency\ClassDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Dependency\ClassSuperglobalDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Dependency\FileDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Dependency\FunctionSuperglobalDependencyEmitter;
use Deptrac\Deptrac\DefaultBehavior\Dependency\UsesDependencyEmitter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DependencyResolverTest extends TestCase
{
    private EventDispatcherInterface $dispatcher;
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->container = new ContainerBuilder();
        $this->container->set(EmitterType::CLASS_TOKEN->value, new ClassDependencyEmitter());
        $this->container->set(EmitterType::CLASS_SUPERGLOBAL_TOKEN->value, new ClassSuperglobalDependencyEmitter());
        $this->container->set(EmitterType::FILE_TOKEN->value, new FileDependencyEmitter());
        $this->container->set(EmitterType::FUNCTION_TOKEN->value, new FunctionDependencyEmitter());
        $this->container->set(EmitterType::FUNCTION_SUPERGLOBAL_TOKEN->value, new FunctionSuperglobalDependencyEmitter());
        $this->container->set(EmitterType::USE_TOKEN->value, new UsesDependencyEmitter());
    }

    public function testResolveWithDefaultEmitters(): void
    {
        $this->expectNotToPerformAssertions();

        $astMap = new AstMap([]);

        $this->dispatcher->method('dispatch')->willReturnOnConsecutiveCalls(
            new PreEmitEvent('ClassDependencyEmitter'),
            new PostEmitEvent(),
            new PreEmitEvent('UsesDependencyEmitter'),
            new PostEmitEvent(),
            new PreFlattenEvent(),
            new PostFlattenEvent()
        );

        $resolver = new DependencyResolver(
            ['types' => [
                EmitterType::CLASS_TOKEN->value,
                EmitterType::USE_TOKEN->value,
            ]],
            $this->container,
            $this->dispatcher
        );

        $resolver->resolve($astMap);
    }

    public function testResolveWithCustomEmitters(): void
    {
        $this->expectNotToPerformAssertions();

        $astMap = new AstMap([]);

        $this->dispatcher->method('dispatch')->willReturnOnConsecutiveCalls(
            new PreEmitEvent('FunctionDependencyEmitter'),
            new PostEmitEvent(),
            new PreFlattenEvent(),
            new PostFlattenEvent()
        );

        $resolver = new DependencyResolver(
            ['types' => [EmitterType::FUNCTION_TOKEN->value]],
            $this->container,
            $this->dispatcher
        );

        $resolver->resolve($astMap);
    }

    public function testResolveWithInvalidEmitterType(): void
    {
        $astMap = new AstMap([]);

        $this->dispatcher->expects(self::never())->method('dispatch');

        $resolver = new DependencyResolver(
            ['types' => ['invalid']],
            $this->container,
            $this->dispatcher
        );

        $this->expectException(InvalidEmitterConfigurationException::class);

        $resolver->resolve($astMap);
    }

    public function testFlattenDependencies(): void
    {
        $astMap = $this->createMock(AstMap::class);

        $astMap->method('getClassLikeReferences')->willReturn([
            $this->getAstClassReference('classA'),
            $this->getAstClassReference('classB'),
            $this->getAstClassReference('classBaum'),
            $this->getAstClassReference('classWeihnachtsbaum'),
            $this->getAstClassReference('classGeschmückterWeihnachtsbaum'),
        ]);

        $dependencyResult = new DependencyList();
        $dependencyResult->addDependency($this->getDependency('classA'));
        $dependencyResult->addDependency($this->getDependency('classB'));
        $dependencyResult->addDependency($this->getDependency('classBaum'));
        $dependencyResult->addDependency($this->getDependency('classWeihnachtsbaumsA'));

        $astMap->method('getClassInherits')->willReturnOnConsecutiveCalls(
            // classA
            [],
            // classB
            [],
            // classBaum,
            [],
            // classWeihnachtsbaum
            [
                new AstInherit(
                    ClassLikeToken::fromFQCN('classBaum'), new FileOccurrence('classWeihnachtsbaum.php', 3),
                    AstInheritType::USES
                ),
            ],
            // classGeschmückterWeihnachtsbaum
            [
                (new AstInherit(
                    ClassLikeToken::fromFQCN('classBaum'), new FileOccurrence('classGeschmückterWeihnachtsbaum.php', 3),
                    AstInheritType::EXTENDS
                ))
                    ->replacePath([
                        new AstInherit(
                            ClassLikeToken::fromFQCN('classWeihnachtsbaum'),
                            new FileOccurrence('classBaum.php', 3),
                            AstInheritType::USES
                        ),
                    ]),
            ]
        );

        DependencyResolver::flattenDependencies($astMap, $dependencyResult);

        $inheritDeps = array_filter(
            $dependencyResult->getDependenciesAndInheritDependencies(),
            static function ($v) {
                return $v instanceof InheritDependency;
            }
        );

        self::assertCount(2, $inheritDeps);
    }

    private function getAstClassReference($className)
    {
        $classLikeToken = ClassLikeToken::fromFQCN($className);
        $astClass = new ClassLikeReference($classLikeToken);
        self::assertSame($classLikeToken, $astClass->getToken());

        return $astClass;
    }

    private function getDependency($className)
    {
        $dep = $this->createMock(DependencyInterface::class);
        $dep->method('getDepender')->willReturn(ClassLikeToken::fromFQCN($className));
        $dep->method('getDependent')->willReturn(ClassLikeToken::fromFQCN($className.'_b'));

        return $dep;
    }
}
