<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver\Monitoring;

use MongoDB\BSON\ObjectId;
use DEPTRAC_INTERNAL\MongoDB\Driver\TopologyDescription;
/**
 * @since 1.13.0
 */
final class TopologyChangedEvent
{
    private final function __construct()
    {
    }
    /**
     * Returns the new description for the topology
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologychangedevent.getnewdescription.php
     */
    public final function getNewDescription() : TopologyDescription
    {
    }
    /**
     * Returns the previous description for the topology
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologychangedevent.getpreviousdescription.php
     */
    public final function getPreviousDescription() : TopologyDescription
    {
    }
    /**
     * Returns the topology ID
     * @link https://www.php.net/manual/en/mongodb-driver-monitoring-topologychangedevent.gettopologyid.php
     */
    public final function getTopologyId() : ObjectId
    {
    }
    public final function __wakeup() : void
    {
    }
}
