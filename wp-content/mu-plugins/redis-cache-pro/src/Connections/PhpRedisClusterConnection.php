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

use Generator;

use RedisCachePro\Clients\PhpRedisCluster;
use RedisCachePro\Configuration\Configuration;

/**
 * Distributed systems are hard.
 *
 * @mixin \RedisCachePro\Clients\PhpRedisCluster
 */
class PhpRedisClusterConnection extends PhpRedisConnection
{
    /**
     * The Redis cluster instance.
     *
     * @var \RedisCachePro\Clients\PhpRedisCluster
     */
    protected $client;

    /**
     * Create a new PhpRedis cluster connection.
     *
     * @param  \RedisCachePro\Clients\PhpRedisCluster  $client
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(PhpRedisCluster $client, Configuration $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->log = $this->config->logger;

        $this->setBackoff();
        $this->setSerializer();
        $this->setCompression();
    }

    /**
     * Execute pipelines as atomic `MULTI` transactions.
     *
     * @return object
     */
    public function pipeline()
    {
        return $this->multi();
    }

    /**
     * Hijack `multi()` calls to allow command logging.
     *
     * @param  int  $type
     * @return object
     */
    public function multi(int $type = null)
    {
        return Transaction::multi($this);
    }

    /**
     * Yields all keys matching the given pattern.
     *
     * @param  string|null  $pattern
     * @return \Generator<array<int, mixed>>
     */
    public function listKeys(?string $pattern = null): Generator
    {
        foreach ($this->client->_masters() as $primary) {
            $iterator = null;

            do {
                $keys = $this->client->scan($iterator, $primary, $pattern, 500);

                if (! empty($keys)) {
                    yield $keys;
                }
            } while ($iterator > 0);
        }
    }

    /**
     * Pings first primary node.
     *
     * To ping a specific node, pass name of key as a string, or a hostname and port as array.
     *
     * @param  string|array<mixed>  $parameter
     * @return bool
     */
    public function ping($parameter = null)
    {
        if (\is_null($parameter)) {
            $primaries = $this->client->_masters();
            $parameter = \reset($primaries);
        }

        return $this->command('ping', [$parameter]);
    }

    /**
     * Fetches information from the first primary node.
     *
     * To fetch information from a specific node, pass name of key as a string, or a hostname and port as array.
     *
     * @param  string|array<mixed>  $parameter
     * @return bool
     */
    public function info($parameter = null)
    {
        if (\is_null($parameter)) {
            $primaries = $this->client->_masters();
            $parameter = \reset($primaries);
        }

        return $this->command('info', [$parameter]);
    }

    /**
     * Call `EVAL` script one key at a time and on all primaries when needed.
     *
     * @param  string  $script
     * @param  array<mixed>  $args
     * @param  int  $keys
     * @return mixed
     */
    public function eval(string $script, array $args = [], int $keys = 0)
    {
        if ($keys === 0) {
            // Will go to random primary
            return $this->command('eval', [$script, $args, 0]);
        }

        if ($keys === 1) {
            if (strpos($args[0], '{') === false) {
                // Must be run on all primaries
                return $this->evalWithoutHashTag($script, $args, 1);
            }

            // Will be called on the primary matching the hash-tag of the key
            return $this->command('eval', [$script, $args, 1]);
        }

        $results = [];

        foreach (array_slice($args, 0, $keys) as $key) {
            // Call this method recursively for each key
            $results[$key] = $this->eval($script, array_merge([$key], array_slice($args, $keys)), 1);
        }

        return $results;
    }

    /**
     * Call `EVAL` script on all primary nodes.
     *
     * @param  string  $script
     * @param  array<mixed>  $args
     * @param  int  $keys
     * @return mixed
     */
    protected function evalWithoutHashTag(string $script, array $args = [], int $keys = 0)
    {
        $results = [];
        $primaries = $this->client->_masters();

        foreach ($primaries as $primary) {
            $key = $this->randomKey($primary);

            if ($key) {
                $results[] = $this->command('eval', [$script, array_merge([$key], $args, ['use-argv']), 1]);
            }
        }

        return $results;
    }

    /**
     * Return all redis cluster nodes.
     *
     * @return array<string>
     */
    public function nodes()
    {
        $nodes = $this->rawCommand(
            $this->client->_masters()[0],
            'CLUSTER',
            'NODES'
        );

        preg_match_all('/[\w{1,}.\-]+:\d{1,}@\d{1,}/', $nodes, $matches);

        return $matches[0];
    }

    /**
     * Flush all nodes on the Redis cluster.
     *
     * @param  bool|null  $async
     * @return true
     */
    public function flushdb($async = null)
    {
        $useAsync = $async ?? $this->config->async_flush;

        foreach ($this->client->_masters() as $primary) {
            $useAsync
                ? $this->rawCommand($primary, 'flushdb', 'async')
                : $this->command('flushdb', [$primary]);
        }

        return true;
    }
}
