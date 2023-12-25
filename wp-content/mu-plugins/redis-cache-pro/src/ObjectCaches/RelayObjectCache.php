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

namespace RedisCachePro\ObjectCaches;

use RedisCachePro\Connections\RelayConnection;
use RedisCachePro\Configuration\Configuration;

class RelayObjectCache extends PhpRedisObjectCache
{
    /**
     * The connection instance.
     *
     * @var \RedisCachePro\Connections\RelayConnection
     */
    protected $connection;

    /**
     * Create new Relay object cache instance.
     *
     * @param  \RedisCachePro\Connections\RelayConnection  $connection
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  ?\RedisCachePro\ObjectCaches\ObjectCacheMetrics  $metrics
     */
    public function __construct(
        RelayConnection $connection,
        Configuration $config,
        ?ObjectCacheMetrics $metrics = null
    ) {
        $this->setup($config, $connection, $metrics);

        if ($config->relay->listeners && $connection->hasInMemoryCache()) {
            $this->connection->onInvalidated(
                [$this, 'invalidated'],
                $config->prefix ? "{$config->prefix}*" : null
            );

            $this->connection->onFlushed(
                [$this, 'flushed']
            );
        }
    }

    /**
     * Callback for the `invalidated` event to keep the in-memory cache in sync.
     *
     * @param  \Relay\Event  $event
     * @return void
     */
    public function invalidated($event)
    {
        $bits = explode(':', $event->key);

        $this->deleteFromMemory(...array_reverse(array_splice($bits, -2)));
    }

    /**
     * Callback for the `flushed` event to keep the in-memory cache fresh.
     *
     * @return void
     */
    public function flushed()
    {
        $this->flush_runtime();
    }

    /**
     * Returns various information about the object cache.
     *
     * @return object
     */
    public function info()
    {
        $info = parent::info();
        $stats = $this->connection->memoize('stats');

        $meta = [
            'Relay Cache' => 'Disabled',
        ];

        if ($this->connection->hasInMemoryCache()) {
            $meta = [
                'Relay Cache' => 'Enabled',
                'Relay Memory' => sprintf(
                    '%s of %s',
                    size_format($stats['memory']['used'], 2),
                    size_format($stats['memory']['total'], 2)
                ),
                'Relay Eviction' => (string) ini_get('relay.eviction_policy'),
            ];
        }

        $info->meta = $meta + $info->meta;

        return $info;
    }
}
