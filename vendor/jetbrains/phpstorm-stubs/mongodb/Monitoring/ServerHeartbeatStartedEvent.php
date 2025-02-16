<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

/**
 * @since 1.13.0
 */
final class ServerHeartbeatStartedEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the port on which this server is listening
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatstartedevent.getport.php
     */
    public final function getPort() : int
    {
    }
    /**
     * Returns the hostname of the server
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatstartedevent.gethost.php
     */
    public final function getHost() : string
    {
    }
    /**
     * Returns whether the heartbeat used a streaming protocol
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-serverheartbeatstartedevent.isstreaming.php
     */
    public final function isAwaited() : bool
    {
    }
    public final function __wakeup() : void
    {
    }
}
