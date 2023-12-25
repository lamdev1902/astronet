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

namespace RedisCachePro\Connections;

use RedisCachePro\Connectors\RelayConnector;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Exceptions\ConnectionException;

class RelayReplicatedConnection extends RelayConnection implements ConnectionInterface
{
    use Concerns\RedisCommands,
        Concerns\ReplicatedConnection;

    /**
     * The primary connection.
     *
     * @var \RedisCachePro\Connections\RelayConnection
     */
    protected $primary;

    /**
     * An array of replica connections.
     *
     * @var \RedisCachePro\Connections\RelayConnection[]
     */
    protected $replicas;

    /**
     * The pool of connections for read commands.
     *
     * @var \RedisCachePro\Connections\RelayConnection[]
     */
    protected $pool;

    /**
     * Create a new replicated Relay connection.
     *
     * @param  \RedisCachePro\Connections\RelayConnection  $primary
     * @param  \RedisCachePro\Connections\RelayConnection[]  $replicas
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(RelayConnection $primary, array $replicas, Configuration $config)
    {
        $this->primary = $primary;
        $this->replicas = $replicas;

        $this->config = $config;
        $this->log = $this->config->logger;

        if (empty($this->replicas)) {
            $this->discoverReplicas();
        }

        $this->setPool();
    }

    /**
     * Discovers and connects to the replicas from the primary's configuration.
     *
     * @return void
     */
    protected function discoverReplicas()
    {
        $info = $this->primary->info('replication');

        if (! is_array($info)) {
            throw new ConnectionException('Unable to discover replicas');
        }

        if (! in_array($info['role'], ['primary', 'master'])) {
            throw new ConnectionException("Replicated primary is a {$info['role']}");
        }

        foreach ($info as $key => $value) {
            if (strpos((string) $key, 'slave') !== 0) {
                continue;
            }

            $replica = null;

            if (preg_match('/ip=(?P<host>.*),port=(?P<port>\d+)/', $value, $replica)) {
                $config = clone $this->config;
                $config->setHost($replica['host']);
                $config->setPort((int) $replica['port']);

                $this->replicas[] = RelayConnector::connectToInstance($config);
            }
        }
    }

    /**
     * Returns the primary's node information.
     *
     * @return \RedisCachePro\Connections\RelayConnection
     */
    public function primary()
    {
        return $this->primary;
    }

    /**
     * Returns the primary's node information.
     *
     * @deprecated 1.17.0
     * @see \RedisCachePro\Connections\RelayReplicatedConnection::primary()
     *
     * @return \RedisCachePro\Connections\RelayConnection
     */
    public function master()
    {
        return $this->primary;
    }

    /**
     * Returns the replica nodes information.
     *
     * @return \RedisCachePro\Connections\RelayConnection[]
     */
    public function replicas()
    {
        return $this->replicas;
    }
}
