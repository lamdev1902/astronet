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

use RedisCachePro\Clients\Relay;
use RedisCachePro\Clients\ClientInterface;

use RedisCachePro\Connectors\RelayConnector;
use RedisCachePro\Configuration\Configuration;

/**
 * @mixin \RedisCachePro\Clients\Relay
 */
class RelayConnection extends PhpRedisConnection implements ConnectionInterface
{
    /**
     * The Relay client.
     *
     * @var \RedisCachePro\Clients\Relay
     */
    protected $client;

    /**
     * Create a new Relay instance connection.
     *
     * @param  \RedisCachePro\Clients\Relay  $client
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(Relay $client, Configuration $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->log = $this->config->logger;

        $this->setSerializer();
        $this->setCompression();

        if (RelayConnector::supports('backoff')) {
            $this->setBackoff();
        }

        $this->setRelayOptions();
    }

    /**
     * Set the connection's Relay specific options.
     *
     * @return void
     */
    protected function setRelayOptions()
    {
        if ($this->config->relay->invalidations === false) {
            $this->client->setOption($this->client::OPT_CLIENT_INVALIDATIONS, false);
        }

        if (is_array($this->config->relay->allowed) && RelayConnector::supports('allow-patterns')) {
            $this->client->setOption($this->client::OPT_ALLOW_PATTERNS, $this->config->relay->allowed);
        }

        if (is_array($this->config->relay->ignored)) {
            $this->client->setOption($this->client::OPT_IGNORE_PATTERNS, $this->config->relay->ignored);
        }
    }

    /**
     * Returns the connection's client.
     *
     * @return \RedisCachePro\Clients\Relay
     */
    public function client(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Dispatch invalidation events.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return int|false
     */
    public function dispatchEvents()
    {
        return $this->client->dispatchEvents();
    }

    /**
     * Registers an event listener.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @param  callable  $callback
     * @return bool
     */
    public function listen(callable $callback)
    {
        return $this->client->listen($callback);
    }

    /**
     * Registers an event listener for flushes.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @param  callable  $callback
     * @return bool
     */
    public function onFlushed(callable $callback)
    {
        return $this->client->onFlushed($callback);
    }

    /**
     * Registers an event listener for invalidations.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @param  callable  $callback
     * @param  string  $pattern
     * @return bool
     */
    public function onInvalidated(callable $callback, string $pattern = null)
    {
        return $this->client->onInvalidated($callback, $pattern);
    }

    /**
     * Returns the number of bytes allocated, or `0` in client-only mode.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return int
     */
    public function maxMemory()
    {
        return $this->client->maxMemory();
    }

    /**
     * Returns statistics about Relay.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return array<mixed>
     */
    public function stats()
    {
        return $this->client->stats();
    }

    /**
     * Returns information about the Relay license.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return array<mixed>
     */
    public function license()
    {
        return $this->client->license();
    }

    /**
     * Returns the connections endpoint identifier.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return string|false
     */
    public function endpointId()
    {
        return $this->client->endpointId();
    }

    /**
     * Flush the selected Redis database.
     *
     * Relay will always use asynchronous flushing, regardless of
     * the `async_flush` configuration option or `$async` parameter.
     *
     * @param  bool|null  $async
     * @return bool
     */
    public function flushdb($async = null)
    {
        $asyncValue = \version_compare((string) \phpversion('relay'), '0.6.9', '<')
            ? true // Relay < 0.6.9
            : false; // Relay >= 0.6.9

        return $this->command('flushdb', [$asyncValue]);
    }

    /**
     * Returns the memoized result from the given command.
     *
     * @param  string  $command
     * @return mixed
     */
    public function memoize($command)
    {
        if ($command === 'ping') {
            /** @var int|false $idleTime */
            $idleTime = $this->client->idleTime();

            return $idleTime > 1000 ? $this->client->ping() : true;
        }

        return parent::memoize($command);
    }

    /**
     * Whether the Relay connection uses in-memory caching, or is only a client.
     *
     * @return bool
     */
    public function hasInMemoryCache()
    {
        static $cache = null;

        if (is_null($cache)) {
            $cache = $this->config->relay->cache && $this->maxMemory() > 0;
        }

        return $cache;
    }
}
