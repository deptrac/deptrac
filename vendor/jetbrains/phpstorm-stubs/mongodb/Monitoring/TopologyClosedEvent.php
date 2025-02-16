<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
/**
 * @since 1.13.0
 */
final class TopologyClosedEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the topology ID
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologyclosedevent.gettopologyid.php
     */
    public final function getTopologyId() : ObjectId
    {
    }
    public final function __wakeup() : void
    {
    }
}
