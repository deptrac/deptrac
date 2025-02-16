<?php

namespace DEPTRAC_INTERNAL\MongoDB\BSON;

/**
 * @since 1.16.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-packedarray.php
 */
final class PackedArray implements \IteratorAggregate, \Serializable
{
    private function __construct()
    {
    }
    public static final function fromPHP(array $value) : PackedArray
    {
    }
    public final function get(int $index) : mixed
    {
    }
    public final function getIterator() : Iterator
    {
    }
    public final function has(int $index) : bool
    {
    }
    public final function toPHP(?array $typeMap = null) : array|object
    {
    }
    /** @since 1.17.0 */
    public function offsetExists(mixed $offset) : bool
    {
    }
    /** @since 1.17.0 */
    public function offsetGet(mixed $offset) : mixed
    {
    }
    /** @since 1.17.0 */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
    }
    /** @since 1.17.0 */
    public function offsetUnset(mixed $offset) : void
    {
    }
    public final function __toString() : string
    {
    }
    public static final function __set_state(array $properties) : PackedArray
    {
    }
    public final function serialize() : string
    {
    }
    public final function unserialize(string $data) : void
    {
    }
    public final function __unserialize(array $data) : void
    {
    }
    public final function __serialize() : array
    {
    }
}
