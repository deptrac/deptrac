<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver;

use MongoDB\BSON\Serializable;
use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;
use stdClass;
/**
 * WriteConcern controls the acknowledgment of a write operation, specifies the level of write guarantee for Replica Sets.
 */
final class WriteConcern implements Serializable, \Serializable
{
    /**
     * Majority of all the members in the set; arbiters, non-voting members, passive members, hidden members and delayed members are all included in the definition of majority write concern.
     */
    public const MAJORITY = 'majority';
    /**
     * Construct immutable WriteConcern
     * @link https://php.net/manual/en/mongodb-driver-writeconcern.construct.php
     * @param string|int $w
     * @param int|null $wtimeout How long to wait (in milliseconds) for secondaries before failing.
     * @param bool|null $journal Wait until mongod has applied the write to the journal.
     * @throws InvalidArgumentException on argument parsing errors.
     */
    public final function __construct(string|int $w, ?int $wtimeout = null, ?bool $journal = null)
    {
    }
    public static function __set_state(array $properties)
    {
    }
    /**
     * Returns the WriteConcern's "journal" option
     * @link https://php.net/manual/en/mongodb-driver-writeconcern.getjournal.php
     */
    public final function getJournal() : ?bool
    {
    }
    /**
     * Returns the WriteConcern's "w" option
     * @link https://php.net/manual/en/mongodb-driver-writeconcern.getw.php
     */
    public final function getW() : string|int|null
    {
    }
    /**
     * Returns the WriteConcern's "wtimeout" option
     * @link https://php.net/manual/en/mongodb-driver-writeconcern.getwtimeout.php
     */
    public final function getWtimeout() : int
    {
    }
    /**
     * Returns an object for BSON serialization
     * @since 1.2.0
     * @link https://www.php.net/manual/en/mongodb-driver-writeconcern.bsonserialize.php
     * @return array|object Returns an object for serializing the WriteConcern as BSON.
     * @throws InvalidArgumentException
     */
    public final function bsonSerialize() : stdClass
    {
    }
    /**
     * Serialize a WriteConcern
     * @since 1.7.0
     * @link https://php.net/manual/en/mongodb-driver-writeconcern.serialize.php
     * @throws InvalidArgumentException
     */
    public final function serialize() : string
    {
    }
    /**
     * Unserialize a WriteConcern
     * @since 1.7.0
     * @link https://php.net/manual/en/mongodb-driver-writeconcern.unserialize.php
     * @throws InvalidArgumentException on argument parsing errors or if the properties are invalid
     * @throws UnexpectedValueException if the properties cannot be unserialized (i.e. serialized was malformed)
     */
    public final function unserialize(string $data) : void
    {
    }
    public final function isDefault() : bool
    {
    }
}
