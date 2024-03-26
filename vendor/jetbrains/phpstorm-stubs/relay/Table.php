<?php

namespace DEPTRAC_202404\Relay;

/**
 * Relay Table is a persistent per-worker hash table that can store arbitrary data.
 */
class Table
{
    /**
     * Create a table instance.
     *
     * @param  int  $serializer
     */
    public function __construct(int $serializer = \DEPTRAC_202404\Relay\Relay::SERIALIZER_PHP)
    {
    }
    /**
     * Get a key from the table.
     * Will return `null` if key doesn't exist.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get(string $key) : mixed
    {
    }
    /**
     * Get a field of a cached key.  This is an array lookup.
     *
     * @param  string  $key
     * @param  string  $field
     * @return mixed
     */
    public function getField(string $key, string $field) : mixed
    {
    }
    /**
     * Set a key in the table.
     *
     * @param  string  $key
     * @param  mixed  $value;
     * @return bool
     */
    public function set(string $key, mixed $value) : bool
    {
    }
    /**
     * Check if a key exists in the table.
     *
     * @param  string  $key
     * @return bool
     */
    public function exists(string $key) : bool
    {
    }
    /**
     * Remove a key from the table.
     *
     * @param  string  $key
     * @return bool
     */
    public function delete(string $key) : bool
    {
    }
    /**
     * Removes all keys from the table.
     *
     * @return bool
     */
    public function clear() : bool
    {
    }
    /**
     * Get the number of keys stored in the table.
     *
     * @return int
     */
    public function count() : int
    {
    }
}
