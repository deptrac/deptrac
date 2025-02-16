<?php

namespace DEPTRAC_INTERNAL\MongoDB\BSON;

use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
/**
 * Class Regex
 * @link https://php.net/manual/en/class.mongodb-bson-regex.php
 */
final class Regex implements Type, RegexInterface, \Serializable, JsonSerializable
{
    /**
     * Construct a new Regex
     * @link https://php.net/manual/en/mongodb-bson-regex.construct.php
     */
    public final function __construct(string $pattern, string $flags = '')
    {
    }
    /**
     * Returns the Regex's flags
     * @link https://php.net/manual/en/mongodb-bson-regex.getflags.php
     */
    public final function getFlags() : string
    {
    }
    /**
     * Returns the Regex's pattern
     * @link https://php.net/manual/en/mongodb-bson-regex.getpattern.php
     */
    public final function getPattern() : string
    {
    }
    /**
     * Returns the string representation of this Regex
     * @link https://php.net/manual/en/mongodb-bson-regex.tostring.php
     */
    public final function __toString() : string
    {
    }
    public static function __set_state(array $properties)
    {
    }
    /**
     * Serialize a Regex
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-regex.serialize.php
     * @throws InvalidArgumentException
     */
    public final function serialize() : string
    {
    }
    /**
     * Unserialize a Regex
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-regex.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    public final function unserialize(string $data) : void
    {
    }
    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-regex.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    public final function jsonSerialize()
    {
    }
}
