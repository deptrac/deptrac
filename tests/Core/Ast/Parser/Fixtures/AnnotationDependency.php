<?php

declare(strict_types=1);

namespace Tests\Deptrac\Deptrac\Core\Ast\Parser\Fixtures;

final class AnnotationDependency
{
    /**
     * @var AnnotationDependencyChild
     */
    public $property;

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $parameter
     *
     * @return AnnotationDependencyChild[]
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    public function test($parameter)
    {
        /** @var ?AnnotationDependencyChild $test */
        $test = null;

        /** @var AnnotationDependencyChild[] $children */
        $children = [];

        /** @var \Symfony\Component\Console\Exception\RuntimeException $fqn */
        $fqn = [];

        return [];
    }

    /**
     * @template T
     * @param T $var
     * @return AnnotationDependencyChild<T>
     */
    public function template($var)
    {
        return new AnnotationDependencyChild($var);
    }
}

/**
 * @template T
 */
final class AnnotationDependencyChild
{
    /**
     * @param T $var
     */
    public function __construct($var)
    {
    }

    /**
     * @return T
     */
    public function get()
    {

    }

    /**
     * @param int<0, max> $rangeParameter
     */
    public function intRangeTest($rangeParameter)
    {

    }
}

trait TestTrait
{

    /**
     * @var \DateTimeImmutable[] $array
     */
    private array $times = [];

    private function getTime(): int
    {
        /** @var \DateTimeImmutable[] $array */
        $array = [new \DateTimeImmutable()];

        return 123;
    }
}

/**
 * @template-covariant TEntityId
 */
interface TemplateInterface
{
    /**
     * @return TEntityId
     */
    public function getId(): mixed;

}

/**
 * @phpstan-type arrayAlias array<string, mixed>
 */
class SomeClass
{
    /**
     * @return arrayAlias
     */
    private function someMethod(): array
    {
        return [];
    }
}
