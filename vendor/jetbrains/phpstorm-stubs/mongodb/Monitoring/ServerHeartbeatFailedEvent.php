<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

/**
 * @since 1.13.0
 */
final class ServerHeartbeatFailedEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the heartbeat's duration in microseconds
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatfailedevent.getdurationmicros.php
     */
    public final function getDurationMicros() : int
    {
    }
    /**
     * Returns the Exception associated with the failed heartbeat
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatfailedevent.geterror.php
     */
    public final function getError() : \Exception
    {
    }
    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatfailedevent.getport.php
     */
    public final function getPort() : int
    {
    }
    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatfailedevent.gethost.php
     */
    public final function getHost() : string
    {
    }
    /**
     * Returns whether the heartbeat used a streaming protocol
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatfailedevent.isstreaming.php
     */
    public final function isAwaited() : bool
    {
    }
    public final function __wakeup() : void
    {
    }
}
