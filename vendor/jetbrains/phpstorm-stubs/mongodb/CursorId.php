<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver;

use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
/**
 * Class CursorId
 * @link https://php.net/manual/en/class.mongodb-driver-cursorid.php
 */
final class CursorId implements \Serializable
{
    /**
     * Create a new CursorId (not used)
     * CursorId objects are returned from Cursor::getId() and cannot be constructed directly.
     * @link https://php.net/manual/en/mongodb-driver-cursorid.construct.php
     * @see Cursor::getId()
     */
    private final function __construct()
    {
    }
    /**
     * String representation of the cursor ID
     * @link https://php.net/manual/en/mongodb-driver-cursorid.tostring.php
     * @return string representation of the cursor ID.
     * @throws InvalidArgumentException on argument parsing errors.
     */
    public final function __toString() : string
    {
    }
    public final function __wakeup()
    {
    }
    public static function __set_state(array $properties)
    {
    }
    /**
     * Serialize a CursorId
     * @since 1.7.0
     * @link https://php.net/manual/en/mongodb-driver-cursorid.serialize.php
     * @throws InvalidArgumentException
     */
    public final function serialize() : string
    {
    }
    /**
     * Unserialize a CursorId
     * @since 1.7.0
     * @link https://php.net/manual/en/mongodb-driver-cursorid.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    public final function unserialize(string $data) : void
    {
    }
}
