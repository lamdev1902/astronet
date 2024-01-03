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

trait Replication
{
    /**
     * The array of replicated Redis servers.
     *
     * @var array<string>
     */
    protected $servers;

    /**
     * The replication strategy.
     *
     * @var string
     */
    protected $replication_strategy = 'distribute';

    /**
     * The available replication strategies.
     *
     * @return array<string>
     */
    protected function replicationStrategies()
    {
        return [
            // Distribute readonly commands between primary and replicas, at random
            'distribute',

            // Distribute readonly commands to the replicas, at random
            'distribute_replicas',

            // Send readonly commands to a single, random replica
            'concentrate',
        ];
    }

    /**
     * Set the array of replicated Redis servers.
     *
     * @param  array<string>  $servers
     * @return void
     */
    public function setServers($servers)
    {
        if (! \is_array($servers)) {
            throw new ConfigurationException('`servers` must an array of Redis servers');
        }

        $primaries = \array_filter($servers, static function ($server) {
            return in_array(static::parseUrl($server)['role'], ['primary', 'master']);
        });

        if (\count($primaries) !== 1) {
            throw new ConfigurationException('`servers` must contain exactly one primary');
        }

        $this->servers = $servers;
    }

    /**
     * Set the replication strategy.
     *
     * @param  string  $strategy
     * @return void
     */
    public function setReplicationStrategy($strategy)
    {
        $strategy = \strtolower((string) $strategy);

        if (! \in_array($strategy, $this->replicationStrategies())) {
            throw new ConfigurationException("Replication strategy `{$strategy}` is not supported");
        }

        $this->replication_strategy = $strategy;
    }
}
