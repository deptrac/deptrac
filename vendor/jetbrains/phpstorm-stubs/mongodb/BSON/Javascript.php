<?php

namespace DEPTRAC_INTERNAL\MongoDB\BSON;

use JsonSerializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
/**
 * Class Javascript
 * @link https://php.net/manual/en/class.mongodb-bson-javascript.php
 */
final class Javascript implements Type, JavascriptInterface, \Serializable, JsonSerializable
{
    /**
     * Construct a new Javascript
     * @link https://php.net/manual/en/mongodb-bson-javascript.construct.php
     */
    public final function __construct(string $javascript, array|object|null $scope = null)
    {
    }
    public static function __set_state(array $properties)
    {
    }
    /**
     * Returns the Javascript's code
     * @link https://secure.php.net/manual/en/mongodb-bson-javascript.getcode.php
     */
    public final function getCode() : string
    {
    }
    /**
     * Returns the Javascript's scope document
     * @link https://secure.php.net/manual/en/mongodb-bson-javascript.getscope.php
     */
    public final function getScope() : ?object
    {
    }
    /**
     * Returns the Javascript's code
     * @link https://secure.php.net/manual/en/mongodb-bson-javascript.tostring.php
     */
    public final function __toString() : string
    {
    }
    /**
     * Serialize a Javascript
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-javascript.serialize.php
     * @throws InvalidArgumentException
     */
    public final function serialize() : string
    {
    }
    /**
     * Unserialize a Javascript
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-javascript.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    public final function unserialize(string $data) : void
    {
    }
    /**
     * Returns a representation that can be converted to JSON
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-bson-javascript.jsonserialize.php
     * @return mixed data which can be serialized by json_encode()
     * @throws InvalidArgumentException on argument parsing errors
     */
    public final function jsonSerialize()
    {
    }
}
