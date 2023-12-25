<?php
/**
 * Copyright © 2019-2023 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Configuration\Concerns;

use RedisCachePro\Exceptions\ConfigurationException;
use RedisCachePro\Exceptions\ConfigurationInvalidException;

trait Cluster
{
    /**
     * The cluster configuration name as string, or an array of cluster nodes.
     *
     * @var string|array<string>|null
     */
    protected $cluster;

    /**
     * The cluster failover strategy.
     *
     * @var string
     */
    protected $cluster_failover = 'error';

    /**
     * The available cluster failover strategies.
     *
     * @return array<string>
     */
    protected function clusterFailovers()
    {
        return [
            // Only send commands to primary nodes
            'none',

            // If a primary can't be reached, and it has replicas, failover for read commands
            'error',

            // Always distribute readonly commands between primaries and replicas, at random
            'distribute',

            // Always distribute readonly commands to the replicas, at random
            'distribute_replicas',
        ];
    }

    /**
     * Set the cluster configuration name or an array of cluster nodes.
     *
     * @param  string|array<string>  $cluster
     * @return void
     */
    public function setCluster($cluster)
    {
        if (is_null($cluster)) {
            return;
        }

        if (! \is_string($cluster) && ! \is_array($cluster)) {
            throw new ConfigurationException(
                '`cluster` must be a configuration name (string) or an array of cluster nodes'
            );
        }

        if (empty($cluster)) {
            throw new ConfigurationInvalidException('`cluster` must be a non-empty string or array');
        }

        $this->cluster = $cluster;
    }

    /**
     * Set the automatic replica failover / distribution.
     *
     * @param  string  $failover
     * @return void
     */
    public function setClusterFailover($failover)
    {
        $failover = \strtolower((string) $failover);
        $failover = \str_replace('distribute_slaves', 'distribute_replicas', $failover);

        if (! \in_array($failover, $this->clusterFailovers())) {
            throw new ConfigurationException("Cluster failover `{$failover}` is not supported");
        }

        $this->cluster_failover = $failover;
    }

    /**
     * Legacy method to set the automatic replica failover / distribution.
     *
     * @param  string  $failover
     * @return void
     */
    public function setSlaveFailover($failover)
    {
        $this->setClusterFailover($failover);
    }

    /**
     * Returns the value of the `RedisCluster::FAILOVER_*` constant.
     *
     * @return  int
     */
    public function getClusterFailover()
    {
        $failover = \str_replace('distribute_replicas', 'distribute_slaves', $this->cluster_failover);
        $failover = \strtoupper($failover);

        return \constant("\RedisCluster::FAILOVER_{$failover}");
    }
}
