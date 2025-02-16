<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

/**
 * @since 1.13.0
 */
final class ServerHeartbeatSucceededEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the heartbeat's duration in microseconds
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.getdurationmicros.php
     */
    public final function getDurationMicros() : int
    {
    }
    /**
     * Returns the heartbeat reply document
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.getreply.php
     */
    public final function getReply() : object
    {
    }
    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.getport.php
     */
    public final function getPort() : int
    {
    }
    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.gethost.php
     */
    public final function getHost() : string
    {
    }
    /**
     * Returns whether the heartbeat used a streaming protocol
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatsucceededevent.isstreaming.php
     */
    public final function isAwaited() : bool
    {
    }
    public final function __wakeup() : void
    {
    }
}
