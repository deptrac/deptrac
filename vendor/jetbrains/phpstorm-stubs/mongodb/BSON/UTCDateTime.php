<?php

namespace DEPTRAC_INTERNAL\MongoDB\BSON;

use DateTimeInterface;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
/**
 * Represents a BSON date.
 * @link https://php.net/manual/en/class.mongodb-bson-utcdatetime.php
 */
final class UTCDateTime implements Type, UTCDateTimeInterface, \Serializable, \JsonSerializable
{
    /**
     * Construct a new UTCDateTime
     * @link https://php.net/manual/en/mongodb-bson-utcdatetime.construct.php
     */
    public final function __construct(int|string|float|DateTimeInterface|null $milliseconds = null)
    {
    }
    public static function __set_state(array $properties)
    {
    }
    /**
     * Returns the DateTime representation of this UTCDateTime
     * @link https://php.net/manual/en/mongodb-bson-utcdatetime.todatetime.php
     */
    public final function toDateTime() : \DateTime
    {
    }
    /**
     * Returns the string representation of this UTCDateTime
     * @link https://php.net/manual/en/mongodb-bson-utcdatetime.tostring.php
     */
    public final function __toString() : string
    {
    }
    /**
     * Serialize a UTCDateTime
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-utcdatetime.serialize.php
     * @throws InvalidArgumentException
     */
    public final function serialize() : string
    {
    }
    /**
     * Unserialize a UTCDateTime
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-utcdatetime.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    public final function unserialize(string $data) : void
    {
    }
    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-utcdatetime.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    public final function jsonSerialize()
    {
    }
}
