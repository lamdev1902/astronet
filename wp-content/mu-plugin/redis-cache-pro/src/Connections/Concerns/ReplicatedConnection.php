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

namespace RedisCachePro\Connections\Concerns;

use Generator;
use Throwable;

use RedisCachePro\Clients\ClientInterface;
use RedisCachePro\Connections\Transaction;
use RedisCachePro\Exceptions\ConnectionException;

trait ReplicatedConnection
{
    /**
     * Returns the connection's client.
     *
     * @return \RedisCachePro\Clients\ClientInterface
     */
    public function client(): ClientInterface
    {
        return $this->primary()->client();
    }

    /**
     * Run a command against Redis.
     *
     * @param  string  $name
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function command(string $name, array $parameters = [])
    {
        $this->lastCommand = null;

        $isReading = \in_array(\strtoupper($name), $this->readonly);

        // send `alloptions` read requests to the primary
        if ($isReading && \is_string($parameters[0] ?? null)) {
            $isReading = \strpos($parameters[0], 'options:alloptions') === false;
        }

        $node = $isReading
            ? $this->pool[\array_rand($this->pool)]
            : $this->primary;

        $result = $node->command($name, $parameters);

        $this->lastCommand = $node->lastCommand;

        return $result;
    }

    /**
     * Execute all `pipeline()` calls on primary node.
     *
     * @return object
     */
    public function pipeline()
    {
        return Transaction::pipeline($this->primary);
    }

    /**
     * Execute all `multi()` calls on primary node.
     *
     * @param  int  $type
     * @return object
     */
    public function multi(int $type = null)
    {
        return $type === $this->client::PIPELINE
            ? Transaction::multi($this->primary)
            : Transaction::pipeline($this->primary);
    }

    /**
     * Send `scan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function scan(&$iterator, $match = null, $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->scan($iterator, $match, $count);
    }

    /**
     * Send `hscan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function hscan($key, &$iterator, $match = null, int $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->hscan($key, $iterator, $match, $count);
    }

    /**
     * Send `sscan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function sscan($key, &$iterator, $match = null, int $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->sscan($key, $iterator, $match, $count);
    }

    /**
     * Send `zscan()` calls directly to random node from pool to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function zscan($key, &$iterator, $match = null, int $count = 0)
    {
        $replica = key($this->pool);

        return $this->pool[$replica]->zscan($key, $iterator, $match, $count);
    }

    /**
     * Yields all keys matching the given pattern.
     *
     * @param  string|null  $pattern
     * @return \Generator<array<int, mixed>>
     */
    public function listKeys(?string $pattern = null): Generator
    {
        $replica = key($this->pool);
        $iterator = null;

        do {
            $keys = $this->pool[$replica]->scan($iterator, $pattern, 500);

            if (! empty($keys)) {
                yield $keys;
            }
        } while ($iterator > 0);
    }

    /**
     * Execute the callback without data mutations on the connection,
     * such as serialization and compression algorithms.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function withoutMutations(callable $callback)
    {
        $this->primary->unsetSerializer();
        $this->primary->unsetCompression();

        foreach ($this->replicas as $replica) {
            $replica->unsetSerializer();
            $replica->unsetCompression();
        }

        try {
            return $callback($this);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $this->primary->setSerializer();
            $this->primary->setCompression();

            foreach ($this->replicas as $replica) {
                $replica->setSerializer();
                $replica->setCompression();
            }
        }
    }

    /**
     * Execute callback with custom read timeout.
     *
     * @param  callable  $callback
     * @param  mixed  $timeout
     * @return mixed
     */
    public function withTimeout(callable $callback, $timeout)
    {
        $this->primary->setTimeout((string) $timeout);

        foreach ($this->replicas as $replica) {
            $replica->setTimeout((string) $timeout);
        }

        try {
            return $callback($this);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $this->primary->setTimeout((string) $this->config->read_timeout);

            foreach ($this->replicas as $replica) {
                $replica->setTimeout((string) $this->config->read_timeout);
            }
        }
    }

    /**
     * Set the pool based on the config's `replication_strategy`.
     *
     * @return void
     */
    protected function setPool()
    {
        $strategy = $this->config->replication_strategy;

        if ($strategy === 'distribute') {
            $this->pool = array_merge([$this->primary], $this->replicas);

            return;
        }

        if (empty($this->replicas)) {
            throw new ConnectionException(
                "No replicas configured/discovered for `{$strategy}` replication strategy"
            );
        }

        if ($strategy === 'distribute_replicas') {
            $this->pool = $this->replicas;
        }

        if ($strategy === 'concentrate') {
            $this->pool = [$this->replicas[array_rand($this->replicas)]];
        }
    }
}
