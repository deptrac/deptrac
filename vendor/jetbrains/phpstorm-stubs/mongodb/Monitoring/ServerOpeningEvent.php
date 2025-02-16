<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
/**
 * @since 1.13.0
 */
final class ServerOpeningEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serveropeningevent.getport.php
     */
    public final function getPort() : int
    {
    }
    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serveropeningevent.gethost.php
     */
    public final function getHost() : string
    {
    }
    /**
     * Returns the topology ID associated with this server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serveropeningevent.gettopologyid.php
     */
    public final function getTopologyId() : ObjectId
    {
    }
    public final function __wakeup() : void
    {
    }
}
