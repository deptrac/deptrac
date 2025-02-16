<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver;

/**
 * The MongoDB\Driver\WriteResult class encapsulates information about an executed MongoDB\Driver\BulkWrite and may be returned by MongoDB\Driver\Manager::executeBulkWrite().
 * @link https://php.net/manual/en/class.mongodb-driver-writeresult.php
 */
final class WriteResult
{
    private final function __construct()
    {
    }
    public final function __wakeup()
    {
    }
    /**
     * Returns the number of documents deleted
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getdeletedcount.php
     */
    public final function getDeletedCount() : ?int
    {
    }
    /**
     * Returns the number of documents inserted (excluding upserts)
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getinsertedcount.php
     */
    public final function getInsertedCount() : ?int
    {
    }
    /**
     * Returns the number of documents selected for update
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getmatchedcount.php
     */
    public final function getMatchedCount() : ?int
    {
    }
    /**
     * Returns the number of existing documents updated
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getmodifiedcount.php
     */
    public final function getModifiedCount() : ?int
    {
    }
    /**
     * Returns the server associated with this write result
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getserver.php
     */
    public final function getServer() : Server
    {
    }
    /**
     * Returns the number of documents inserted by an upsert
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getupsertedcount.php
     */
    public final function getUpsertedCount() : ?int
    {
    }
    /**
     * Returns an array of identifiers for upserted documents
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getupsertedids.php
     */
    public final function getUpsertedIds() : array
    {
    }
    /**
     * Returns any write concern error that occurred
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getwriteconcernerror.php
     */
    public final function getWriteConcernError() : ?WriteConcernError
    {
    }
    /**
     * Returns any write errors that occurred
     * @link https://php.net/manual/en/mongodb-driver-writeresult.getwriteerrors.php
     * @return WriteError[]
     */
    public final function getWriteErrors() : array
    {
    }
    /**
     * @since 1.16.0
     */
    public final function getErrorReplies() : array
    {
    }
    /**
     * Returns whether the write was acknowledged
     * @link https://php.net/manual/en/mongodb-driver-writeresult.isacknowledged.php
     */
    public final function isAcknowledged() : bool
    {
    }
}
