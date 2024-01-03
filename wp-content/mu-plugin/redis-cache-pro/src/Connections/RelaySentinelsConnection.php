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

use Relay\Sentinel;

use RedisCachePro\Clients\RelaySentinel;
use RedisCachePro\Connectors\RelayConnector;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Exceptions\ConnectionException;

class RelaySentinelsConnection extends RelayReplicatedConnection implements ConnectionInterface
{
    use Concerns\SentinelsConnection;

    /**
     * The current Sentinel node.
     *
     * @var string
     */
    protected $sentinel;

    /**
     * Holds all Sentinel states and URLs.
     *
     * If the state is `null` no connection has been established.
     * If the state is `false` the connection failed or a timeout occurred.
     * If the state is a `RedisSentinel` object it's the current Sentinel node.
     *
     * @var array<mixed>
     */
    protected $sentinels;

    /**
     * Create a new PhpRedis Sentinel connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(Configuration $config)
    {
        $this->config = $config;

        foreach ($config->sentinels as $sentinel) {
            $this->sentinels[$sentinel] = null;
        }

        $this->connectToSentinels();
    }

    /**
     * Establish a connection to the given Redis Sentinel and its primary and replicas.
     *
     * @param  string  $url
     * @return void
     */
    protected function establishConnections(string $url)
    {
        $config = clone $this->config;
        $config->setUrl($url);

        $persistentId = '';

        $arguments = [
            $config->host,
            $config->port,
            $config->timeout,
            $persistentId,
            $config->retry_interval,
            $config->read_timeout,
        ];

        if ($config->password) {
            $arguments[] = $config->username
                ? [$config->username, $config->password]
                : $config->password;
        }

        $this->sentinels[$url] = new RelaySentinel(function () use ($arguments) {
            return new Sentinel(...$arguments);
        }, $config->tracer);

        $this->discoverPrimary();
        $this->discoverReplicas();
    }

    /**
     * Discovers and connects to the Sentinel primary.
     *
     * @return void
     */
    protected function discoverPrimary()
    {
        $primary = $this->sentinel()->getMasterAddrByName($this->config->service);

        if (! $primary) {
            throw new ConnectionException("Failed to retrieve sentinel primary of `{$this->sentinel}`");
        }

        $config = clone $this->config;
        $config->setHost($primary[0]);
        $config->setPort($primary[1]);

        $connection = RelayConnector::connectToInstance($config);

        /** @var array<int, mixed> $role */
        $role = $connection->role();

        if (($role[0] ?? null) !== 'master') {
            throw new ConnectionException("Sentinel primary of `{$this->sentinel}` is not a primary");
        }

        $this->primary = $connection;
        $this->client = $connection->client();
    }

    /**
     * Discovers and connects to the Sentinel replicas.
     *
     * @return void
     */
    protected function discoverReplicas()
    {
        $replicas = $this->sentinel()->slaves($this->config->service);

        if (! $replicas) {
            throw new ConnectionException("Failed to discover Sentinel replicas of `{$this->sentinel}`");
        }

        foreach ($replicas as $replica) {
            if (($replica['role-reported'] ?? '') !== 'slave') {
                continue;
            }

            $config = clone $this->config;
            $config->setHost($replica['ip']);
            $config->setPort($replica['port']);

            $this->replicas[$replica['name']] = RelayConnector::connectToInstance($config);
        }
    }

    /**
     * Returns the current Sentinel connection.
     *
     * @return \RedisCachePro\Clients\RelaySentinel
     */
    public function sentinel()
    {
        return $this->sentinels[$this->sentinel];
    }
}
