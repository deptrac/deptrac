<?php

namespace DEPTRAC_INTERNAL\MongoDB\Driver;

/**
 * @since 1.13.0
 */
class TopologyDescription
{
    public const TYPE_UNKNOWN = 'Unknown';
    public const TYPE_SINGLE = 'Single';
    public const TYPE_SHARDED = 'Sharded';
    public const TYPE_REPLICA_SET_NO_PRIMARY = 'ReplicaSetNoPrimary';
    public const TYPE_REPLICA_SET_WITH_PRIMARY = 'ReplicaSetWithPrimary';
    public const TYPE_LOAD_BALANCED = 'LoadBalanced';
    private final function __construct()
    {
    }
    /**
     * Returns the servers in the topology
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.getservers.php
     * @return ServerDescription[]
     */
    public final function getServers() : array
    {
    }
    /**
     * Returns a string denoting the type of this topology
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.gettype.php
     */
    public final function getType() : string
    {
    }
    /**
     * Returns whether the topology has a readable server
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.hasreadableserver.php
     */
    public final function hasReadableServer(?ReadPreference $readPreference = null) : bool
    {
    }
    /**
     * Returns whether the topology has a writable server
     * @link https://www.php.net/manual/en/mongodb-driver-topologydescription.haswritableserver.php
     */
    public final function hasWritableServer() : bool
    {
    }
    public final function __wakeup() : void
    {
    }
}
