<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
use DEPTRAC_INTERNAL\MongoDB\Driver\ServerDescription;
/**
 * @since 1.13.0
 */
final class ServerChangedEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.getport.php
     */
    public final function getPort() : int
    {
    }
    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.gethost.php
     */
    public final function getHost() : string
    {
    }
    /**
     * Returns the new description for the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.getnewdescription.php
     */
    public final function getNewDescription() : ServerDescription
    {
    }
    /**
     * Returns the previous description for the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.getpreviousdescription.php
     */
    public final function getPreviousDescription() : ServerDescription
    {
    }
    /**
     * Returns the topology ID associated with this server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverchangedevent.gettopologyid.php
     */
    public final function getTopologyId() : ObjectId
    {
    }
    public final function __wakeup() : void
    {
    }
}
