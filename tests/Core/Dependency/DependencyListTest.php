<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Dependency;

use Deptrac\Deptrac\Contract\Ast\AstMap\AstInherit;
use Deptrac\Deptrac\Contract\Ast\AstMap\AstInheritType;
use Deptrac\Deptrac\Contract\Ast\AstMap\ClassLikeToken;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyContext;
use Deptrac\Deptrac\Contract\Ast\AstMap\DependencyType;
use Deptrac\Deptrac\Contract\Ast\AstMap\FileOccurrence;
use Deptrac\Deptrac\Core\Dependency\DependencyList;
use Deptrac\Deptrac\Core\Dependency\InheritDependency;
use Deptrac\Deptrac\DefaultBehavior\Dependency\Helpers\Dependency;
use PHPUnit\Framework\TestCase;

final class DependencyListTest extends TestCase
{
    public function testAddDependency(): void
    {
        $classA = ClassLikeToken::fromFQCN('A');
        $classB = ClassLikeToken::fromFQCN('B');
        $classC = ClassLikeToken::fromFQCN('C');

        $dependencyResult = new DependencyList();
        $dependencyResult->addDependency($dep1 = new Dependency($classA, $classB, new DependencyContext(
            new FileOccurrence('a.php', 12), DependencyType::PARAMETER)));
        $dependencyResult->addDependency($dep2 = new Dependency($classB, $classC, new DependencyContext(
            new FileOccurrence('b.php', 12), DependencyType::PARAMETER)));
        $dependencyResult->addDependency($dep3 = new Dependency($classA, $classC, new DependencyContext(
            new FileOccurrence('a.php', 12), DependencyType::PARAMETER)));
        self::assertSame([$dep1, $dep3], $dependencyResult->getDependenciesByClass($classA));
        self::assertSame([$dep2], $dependencyResult->getDependenciesByClass($classB));
        self::assertSame([], $dependencyResult->getDependenciesByClass($classC));
        self::assertCount(3, $dependencyResult->getDependenciesAndInheritDependencies());
    }

    public function testGetDependenciesAndInheritDependencies(): void
    {
        $classA = ClassLikeToken::fromFQCN('A');
        $classB = ClassLikeToken::fromFQCN('B');

        $dependencyResult = new DependencyList();
        $dependencyResult->addDependency($dep1 = new Dependency($classA, $classB, new DependencyContext(
            new FileOccurrence('a.php', 12), DependencyType::PARAMETER)));
        $dependencyResult->addInheritDependency($dep2 = new InheritDependency($classA, $classB, $dep1,
            new AstInherit(
                $classB,
                new FileOccurrence('a.php', 12),
                AstInheritType::EXTENDS
            )
        ));
        self::assertSame([$dep1, $dep2], $dependencyResult->getDependenciesAndInheritDependencies());
    }
}
