<?php

namespace DEPTRAC_INTERNAL\MongoDB\BSON;

/**
 * @since 1.16.0
 * @link https://secure.php.net/manual/en/class.mongodb-bson-document.php
 */
final class Document implements \IteratorAggregate, \Serializable
{
    private function __construct()
    {
    }
    public static final function fromBSON(string $bson) : Document
    {
    }
    public static final function fromJSON(string $json) : Document
    {
    }
    public static final function fromPHP(array|object $value) : Document
    {
    }
    public final function get(string $key) : mixed
    {
    }
    public final function getIterator() : Iterator
    {
    }
    public final function has(string $key) : bool
    {
    }
    public final function toPHP(?array $typeMap = null) : array|object
    {
    }
    public final function toCanonicalExtendedJSON() : string
    {
    }
    public final function toRelaxedExtendedJSON() : string
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
    public static final function __set_state(array $properties) : Document
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
