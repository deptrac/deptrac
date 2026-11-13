<?php

final class MigratedHandle {}

final class DocumentedHandle {}

class GMP {}

class NoResource
{
    public function method(string $string): int {}
}

class MethodProducesResource
{
    /**
     * @return resource
     */
    public function stream() {}
}

class MethodConsumesHandle
{
    public function __construct(MigratedHandle $handle) {}
}

class Child extends MethodProducesResource {}

class Grandchild extends Child {}

interface ProducesResource
{
    /**
     * @return resource
     */
    public function create();
}

interface ExtendsProducesResource extends ProducesResource {}

class Implementation implements Countable, ExtendsProducesResource
{
    public function create() {}

    public function count(): int {}
}

enum EnumImplementation implements ProducesResource
{
    public function create() {}
}

trait TraitWithResource
{
    /**
     * @return resource
     */
    public function stream() {}
}

$anonymous = new class {
    /**
     * @return resource
     */
    public function stream() {}
};
