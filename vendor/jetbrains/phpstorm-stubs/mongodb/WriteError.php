<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver;

/**
 * The MongoDB\Driver\WriteError class encapsulates information about a write error and may be returned as an array element from MongoDB\Driver\WriteResult::getWriteErrors().
 */
final class WriteError
{
    private final function __construct()
    {
    }
    public final function __wakeup()
    {
    }
    /**
     * Returns the WriteError's error code
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getcode.php
     */
    public final function getCode() : int
    {
    }
    /**
     * Returns the index of the write operation corresponding to this WriteError
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getindex.php
     */
    public final function getIndex() : int
    {
    }
    /**
     * Returns additional metadata for the WriteError
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getinfo.php
     */
    public final function getInfo() : ?object
    {
    }
    /**
     * Returns the WriteError's error message
     * @link https://php.net/manual/en/mongodb-driver-writeerror.getmessage.php
     */
    public final function getMessage() : string
    {
    }
}
