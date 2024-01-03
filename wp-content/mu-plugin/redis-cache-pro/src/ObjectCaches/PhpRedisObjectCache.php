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

use Throwable;
use ReflectionClass;

use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connections\PhpRedisConnection;

class PhpRedisObjectCache extends ObjectCache implements MeasuredObjectCacheInterface
{
    use Concerns\KeepsMetadata,
        Concerns\PrefetchesKeys,
        Concerns\FlushesNetworks,
        Concerns\TakesMeasurements,
        Concerns\SplitsAllOptionsIntoHash;

    /**
     * The connection instance.
     *
     * @var \RedisCachePro\Connections\PhpRedisConnection
     */
    protected $connection;

    /**
     * Create new PhpRedis object cache instance.
     *
     * @param  \RedisCachePro\Connections\PhpRedisConnection  $connection
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  ?\RedisCachePro\ObjectCaches\ObjectCacheMetrics  $metrics
     */
    public function __construct(
        PhpRedisConnection $connection,
        Configuration $config,
        ?ObjectCacheMetrics $metrics = null
    ) {
        $this->setup($config, $connection, $metrics);
    }

    /**
     * Adds data to the cache, if the cache key doesn't already exist.
     *
     * @param  int|string  $key
     * @param  mixed  $data
     * @param  string  $group
     * @param  int  $expire
     * @return bool
     */
    public function add($key, $data, string $group = 'default', int $expire = 0): bool
    {
        if (function_exists('wp_suspend_cache_addition') && \wp_suspend_cache_addition()) {
            return false;
        }

        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if ($this->hasInMemory($id, $group)) {
            return false;
        }

        if ($this->isNonPersistentGroup($group)) {
            $this->storeInMemory($id, $data, $group);

            return true;
        }

        try {
            if ($this->isAllOptionsId($id)) {
                return $this->syncAllOptions($id, $data);
            }

            $result = (bool) $this->write($id, $data, $expire, 'NX');

            $this->metrics->write($group);

            if ($result) {
                $this->storeInMemory($id, $data, $group);
            }

            return $result;
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }
    }

    /**
     * Adds multiple values to the cache in one call, if the cache keys doesn't already exist.
     *
     * @param  array<int|string, mixed>  $data
     * @param  string  $group
     * @param  int  $expire
     * @return array<int|string, bool>
     */
    public function add_multiple(array $data, string $group = 'default', int $expire = 0): array
    {
        $ids = [];

        if (empty($data)) {
            return [];
        }

        $data = array_filter($data, function ($key) use ($group, &$ids) {
            return ($id = $this->id($key, $group)) ? (bool) $ids[$key] = $id : false;
        }, ARRAY_FILTER_USE_KEY);

        if (function_exists('wp_suspend_cache_addition') && \wp_suspend_cache_addition()) {
            return array_combine(array_keys($data), array_fill(0, count($data), false));
        }

        $results = [];

        if ($this->isNonPersistentGroup($group)) {
            foreach ($data as $key => $value) {
                $results[$key] = ! $this->hasInMemory($ids[$key], $group);

                if ($results[$key]) {
                    $this->storeInMemory($ids[$key], $value, $group);
                }
            }

            return $results;
        }

        foreach ($data as $key => $value) {
            if ($this->hasInMemory($ids[$key], $group)) {
                $results[$key] = false;
            }
        }

        $remainingData = array_diff_key($data, $results);

        if (empty($remainingData)) {
            return $results;
        }

        try {
            $response = $this->multiwrite($remainingData, $group, $expire, 'NX');
        } catch (Throwable $exception) {
            $this->error($exception);

            return array_combine(array_keys($data), array_fill(0, count($data), false));
        }

        $this->metrics->write($group);

        foreach ($response as $key => $result) {
            if ($result['id'] && $result['response']) {
                $this->storeInMemory($result['id'], $data[$key], $group);
            }

            $results[$key] = $result['response'];
        }

        $order = array_flip(array_keys($data));

        uksort($results, static function ($a, $b) use ($order) {
            return $order[$a] - $order[$b];
        });

        return $results;
    }

    /**
     * Boots the cache.
     *
     * @return bool
     */
    public function boot(): bool
    {
        $this->bootMetadata();

        if (! $this->isMultisite()) {
            $this->prefetch();
        }

        return true;
    }

    /**
     * Closes the cache.
     *
     * @return bool
     */
    public function close(): bool
    {
        $this->storePrefetches();
        $this->storeMeasurements();

        return true;
    }

    /**
     * Decrements numeric cache item's value.
     *
     * @param  int|string  $key
     * @param  int  $offset
     * @param  string  $group
     * @return int|false
     */
    public function decr($key, int $offset = 1, string $group = 'default')
    {
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if ($this->isNonPersistentGroup($group)) {
            if (! $this->hasInMemory($id, $group)) {
                return false;
            }

            $value = $this->getFromMemory($id, $group);
            $value = $this->decrement($value, $offset);

            $this->storeInMemory($id, $value, $group);

            return $value;
        }

        try {
            $value = $this->connection->get($id);

            $this->metrics->read($group);

            if ($value === false) {
                return false;
            }

            $value = $this->decrement($value, $offset);
            $result = $this->connection->set($id, $value);

            $this->metrics->write($group);

            if ($result) {
                $this->storeInMemory($id, $value, $group);
            }

            return $value;
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }
    }

    /**
     * Removes the cache contents matching key and group from the runtime memory cache.
     *
     * @param  int|string  $key
     * @param  string  $group
     * @return bool
     */
    public function deleteFromMemory($key, string $group = 'default')
    {
        if ($deleted = parent::deleteFromMemory($key, $group)) {
            unset($this->prefetch[$group][$key]);
        }

        return $deleted;
    }

    /**
     * Removes the cache contents matching key and group.
     *
     * @param  int|string  $key
     * @param  string  $group
     * @return bool
     */
    public function delete($key, string $group = 'default'): bool
    {
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        $deletedFromMemory = $this->deleteFromMemory($key, $group);

        if ($this->isNonPersistentGroup($group)) {
            return $deletedFromMemory;
        }

        try {
            if ($this->isAllOptionsId($id)) {
                return $this->deleteAllOptions($id);
            }

            $method = $this->config->async_flush ? 'unlink' : 'del';
            $result = (bool) $this->connection->{$method}($id);

            $this->metrics->write($group);

            return $result;
        } catch (Throwable $exception) {
            $this->error($exception);
        }

        return false;
    }

    /**
     * Deletes multiple values from the cache in one call.
     *
     * @param  array<int|string>  $keys
     * @param  string  $group
     * @return array<int|string, bool>
     */
    public function delete_multiple(array $keys, string $group = 'default'): array
    {
        $ids = [];

        if (empty($keys)) {
            return [];
        }

        $keys = array_filter($keys, function ($key) use ($group, &$ids) {
            return ($id = $this->id($key, $group)) ? (bool) $ids[$key] = $id : false;
        });

        $results = [];

        if ($this->isNonPersistentGroup($group)) {
            foreach ($keys as $key) {
                $results[$key] = $this->deleteFromMemory($key, $group);
            }

            return $results;
        }

        foreach ($keys as $key) {
            $results[$key] = $ids[$key];
        }

        $deletes = [];
        $command = $this->config->async_flush ? 'unlink' : 'del';

        try {
            $pipe = $this->connection->pipeline();

            foreach ($results as $key => $id) {
                unset($this->cache[$group][$id]);
                unset($this->prefetch[$group][$key]);

                $deletes[] = $id;
                $pipe->{$command}($id);
            }

            $deletes = array_combine($deletes, array_map('boolval', $pipe->exec()));
        } catch (Throwable $exception) {
            $this->error($exception);

            return array_combine($keys, array_fill(0, count($keys), false));
        }

        $this->metrics->write($group);

        return array_map(static function ($id) use ($deletes) {
            return $deletes[$id];
        }, $results);
    }

    /**
     * Removes all items from Redis and the runtime cache.
     *
     * Will write metadata after flushing.
     * Will dump+restore analytics when enabled.
     *
     * @return bool
     */
    public function flush(): bool
    {
        $this->flush_runtime();

        if ($this->config->analytics->enabled && $this->config->analytics->persist) {
            $measurements = $this->dumpMeasurements();
        }

        try {
            $result = $this->connection->flushdb();

            $this->metrics->flush();
            $this->writeMetadata();
        } catch (Throwable $exception) {
            $result = false;

            $this->error($exception);
        }

        if (! empty($measurements)) {
            $this->restoreMeasurements($measurements);
            unset($measurements);
        }

        return $result;
    }

    /**
     * Removes all cache items from the in-memory runtime cache.
     *
     * @return bool
     */
    public function flush_runtime(): bool
    {
        $this->cache = [];
        $this->prefetch = [];

        return true;
    }

    /**
     * Removes all cache items in given group.
     *
     * @param  string  $group
     * @return bool
     */
    public function flush_group(string $group): bool
    {
        unset($this->cache[$group]);
        unset($this->prefetch[$group]);

        if ($this->isNonPersistentGroup($group)) {
            return true;
        }

        $pattern = $this->id('*', $group);

        if ($pattern === false) {
            return false;
        }

        if ($this->isMultisite && ! $this->isGlobalGroup($group)) {
            $groupId = array_reverse(explode(':', $pattern))[1];
            $pattern = str_replace("{$this->blogId}:{$groupId}", "*:{$groupId}", (string) $pattern);
        }

        try {
            $this->deleteByPattern($pattern, $group);
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }

        return true;
    }

    /**
     * Retrieves the cache contents from the cache by key and group.
     *
     * @param  int|string  $key
     * @param  string  $group
     * @param  bool  $force
     * @param  bool  &$found
     * @return mixed|false
     */
    public function get($key, string $group = 'default', bool $force = false, &$found = null)
    {
        if (! $id = $this->id($key, $group)) {
            $found = false;

            return false;
        }

        $cachedInMemory = $this->hasInMemory($id, $group);

        if ($this->isNonPersistentGroup($group)) {
            if (! $cachedInMemory) {
                $found = false;
                $this->metrics->misses += 1;

                return false;
            }

            $found = true;
            $this->metrics->hits += 1;

            return $this->getFromMemory($id, $group);
        }

        if ($this->prefetched) {
            $this->prefetch[$group][$key] = true;
        }

        if ($cachedInMemory && ! $force) {
            $found = true;
            $this->metrics->hits += 1;

            return $this->getFromMemory($id, $group);
        }

        $found = false;

        try {
            if ($this->isAllOptionsId($id)) {
                $data = $this->getAllOptions($id);
            } else {
                $data = $this->connection->get($id);
            }
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }

        $this->metrics->read($group);

        if ($data === false) {
            $this->metrics->misses += 1;
            $this->metrics->storeMisses += 1;

            return false;
        }

        $found = true;
        $this->metrics->hits += 1;
        $this->metrics->storeHits += 1;

        $this->storeInMemory($id, $data, $group);

        return $data;
    }

    /**
     * Retrieves multiple values from the cache in one call.
     *
     * @param  array<int|string>  $keys
     * @param  string  $group
     * @param  bool  $force
     * @return array<int|string, mixed>
     */
    public function get_multiple(array $keys, string $group = 'default', bool $force = false)
    {
        $ids = [];

        if (empty($keys)) {
            return [];
        }

        $keys = array_filter($keys, function ($key) use ($group, &$ids) {
            return ($id = $this->id($key, $group)) ? (bool) $ids[$key] = $id : false;
        });

        $values = [];

        if ($this->isNonPersistentGroup($group)) {
            foreach ($keys as $key) {
                if ($this->hasInMemory($ids[$key], $group)) {
                    $this->metrics->hits += 1;
                    $values[$key] = $this->getFromMemory($ids[$key], $group);
                } else {
                    $this->metrics->misses += 1;
                    $values[$key] = false;
                }
            }

            return $values;
        }

        if ($this->prefetched) {
            foreach ($keys as $key) {
                $this->prefetch[$group][$key] = true;
            }
        }

        $remainingKeys = [];

        foreach ($keys as $key) {
            $values[$key] = false;

            if (! $force && $this->hasInMemory($ids[$key], $group)) {
                $this->metrics->hits += 1;
                $values[$key] = $this->getFromMemory($ids[$key], $group);
            } else {
                $remainingKeys[] = $key;
            }
        }

        if (empty($remainingKeys)) {
            return $values;
        }

        $payload = array_map(static function ($key) use ($ids) {
            return $ids[$key];
        }, $remainingKeys);

        try {
            $data = $this->connection->mget($payload);

            $this->metrics->read($group);

            if ($data === false) {
                $data = array_fill_keys(array_keys($payload), false);
            }

            foreach ($remainingKeys as $index => $key) {
                $values[$key] = $data[$index];

                if ($data[$index] === false) {
                    $this->metrics->misses += 1;
                    $this->metrics->storeMisses += 1;

                    continue;
                }

                $this->metrics->hits += 1;
                $this->metrics->storeHits += 1;

                if ($this->config->prefetch && ! $this->prefetched) {
                    $this->metrics->prefetches++;
                }

                $this->storeInMemory($payload[$index], $data[$index], $group);
            }
        } catch (Throwable $exception) {
            $this->error($exception);
        }

        return $values;
    }

    /**
     * Whether the key exists in the cache.
     *
     * @param  int|string  $key
     * @param  string  $group
     * @return bool
     */
    public function has($key, string $group = 'default'): bool
    {
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if ($this->hasInMemory($id, $group)) {
            return true;
        }

        if ($this->isNonPersistentGroup($group)) {
            return false;
        }

        try {
            $result = (bool) $this->connection->exists($id);

            $this->metrics->read($group);

            return $result;
        } catch (Throwable $exception) {
            $this->error($exception);
        }

        return false;
    }

    /**
     * Increment numeric cache item's value.
     *
     * @param  int|string  $key
     * @param  int  $offset
     * @param  string  $group
     * @return int|false
     */
    public function incr($key, int $offset = 1, string $group = 'default')
    {
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if ($this->isNonPersistentGroup($group)) {
            if (! $this->hasInMemory($id, $group)) {
                return false;
            }

            $value = $this->getFromMemory($id, $group);
            $value = $this->increment($value, $offset);

            $this->storeInMemory($id, $value, $group);

            return $value;
        }

        try {
            $value = $this->connection->get($id);

            $this->metrics->read($group);

            if ($value === false) {
                return false;
            }

            $value = $this->increment($value, $offset);
            $result = $this->connection->set($id, $value);

            $this->metrics->write($group);

            if ($result) {
                $this->storeInMemory($id, $value, $group);
            }

            return $value;
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }
    }

    /**
     * Replaces the contents of the cache with new data.
     *
     * @param  int|string  $key
     * @param  mixed  $data
     * @param  string  $group
     * @param  int  $expire
     * @return bool
     */
    public function replace($key, $data, string $group = 'default', int $expire = 0): bool
    {
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if ($this->isNonPersistentGroup($group)) {
            if (! $this->hasInMemory($id, $group)) {
                return false;
            }

            $this->storeInMemory($id, $data, $group);

            return true;
        }

        try {
            $result = (bool) $this->write($id, $data, $expire, 'XX');

            $this->metrics->write($group);

            if ($result) {
                $this->storeInMemory($id, $data, $group);
            }

            return $result;
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }
    }

    /**
     * Saves the data to the cache.
     *
     * @param  int|string  $key
     * @param  mixed  $data
     * @param  string  $group
     * @param  int  $expire
     * @return bool
     */
    public function set($key, $data, string $group = 'default', int $expire = 0): bool
    {
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if ($this->isNonPersistentGroup($group)) {
            $this->storeInMemory($id, $data, $group);

            return true;
        }

        try {
            if ($this->isAllOptionsId($id)) {
                return $this->syncAllOptions($id, $data);
            }

            $result = (bool) $this->write($id, $data, $expire);

            $this->metrics->write($group);

            if ($result) {
                $this->storeInMemory($id, $data, $group);
            }

            return $result;
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }
    }

    /**
     * Sets multiple values to the cache in one call.
     *
     * @param  array<int|string, mixed>  $data
     * @param  string  $group
     * @param  int  $expire
     * @return array<int|string, bool>
     */
    public function set_multiple(array $data, string $group = 'default', int $expire = 0): array
    {
        $ids = [];

        if (empty($data)) {
            return [];
        }

        $data = array_filter($data, function ($key) use ($group, &$ids) {
            return ($id = $this->id($key, $group)) ? (bool) $ids[$key] = $id : false;
        }, ARRAY_FILTER_USE_KEY);

        if ($this->isNonPersistentGroup($group)) {
            $results = [];

            foreach ($data as $key => $value) {
                $results[$key] = true;
                $this->storeInMemory($ids[$key], $value, $group);
            }

            return $results;
        }

        try {
            $results = $this->multiwrite($data, $group, $expire);
        } catch (Throwable $exception) {
            $this->error($exception);

            return array_combine(array_keys($data), array_fill(0, count($data), false));
        }

        $this->metrics->write($group);

        foreach ($results as $key => $result) {
            if ($result['id'] && $result['response']) {
                $this->storeInMemory($result['id'], $data[$key], $group);
            }

            $results[$key] = $result['response'];
        }

        return $results;
    }

    /**
     * Switches the internal blog ID.
     *
     * @param  int $blog_id
     * @return bool
     */
    public function switch_to_blog(int $blog_id): bool
    {
        if ($this->isMultisite) {
            $this->setBlogId($blog_id);

            return true;
        }

        return false;
    }

    /**
     * Writes the given key to Redis and enforces the `maxttl` configuration option.
     *
     * @param  string  $id
     * @param  mixed  $data
     * @param  int  $expire
     * @param  string  $option
     * @return bool
     */
    protected function write(string $id, $data, int $expire = 0, $option = null): bool
    {
        if ($expire < 0) {
            $expire = 0;
        }

        $maxttl = $this->config->maxttl;

        if ($maxttl && ($expire === 0 || $expire > $maxttl)) {
            $expire = $maxttl;
        }

        if ($expire && $option) {
            return $this->connection->set($id, $data, [$option, 'EX' => $expire]);
        }

        if ($expire) {
            return $this->connection->setex($id, $expire, $data);
        }

        if ($option) {
            return $this->connection->set($id, $data, [$option]);
        }

        return $this->connection->set($id, $data);
    }

    /**
     * Writes the given keys to Redis and enforces the `maxttl` configuration option.
     *
     * @param  array<int|string, mixed>  $data
     * @param  int  $expire
     * @param  string  $option
     * @return array<int|string, array{id: string|false, response: mixed}>
     */
    protected function multiwrite(array $data, string $group, int $expire = 0, $option = null): array
    {
        if ($expire < 0) {
            $expire = 0;
        }

        $maxttl = $this->config->maxttl;

        if ($maxttl && ($expire === 0 || $expire > $maxttl)) {
            $expire = $maxttl;
        }

        $results = [];

        $pipe = $this->connection->pipeline();

        foreach ($data as $key => $value) {
            if (! $id = $this->id($key, $group)) {
                $results[$key] = ['id' => false, 'response' => false];
                continue;
            }

            $results[$key] = ['id' => $id, 'response' => false];

            if ($expire && $option) {
                $pipe->set($id, $value, [$option, 'EX' => $expire]);
                continue;
            }

            if ($expire) {
                $pipe->setex($id, $expire, $value);
                continue;
            }

            if ($option) {
                $pipe->set($id, $value, [$option]);
                continue;
            }

            $pipe->set($id, $value);
        }

        $keys = array_keys($results);

        foreach ($pipe->exec() as $i => $result) {
            $results[$keys[$i]]['response'] = $result;
        }

        return $results;
    }

    /**
     * Returns various information about the object cache.
     *
     * @return \RedisCachePro\Support\ObjectCacheInfo
     */
    public function info()
    {
        $server = $this->connection->memoize('info');

        $info = parent::info();
        $info->status = (bool) $this->connection->memoize('ping');
        $info->meta = array_filter([
            'Redis Version' => $server['redis_version'],
            'Redis Memory' => size_format($server['used_memory'], 2),
            'Redis Eviction' => $server['maxmemory_policy'] ?? null,
            'Cache' => (new ReflectionClass($this))->getShortName(),
            'Client' => (new ReflectionClass($this->client()))->getShortName(),
            'Connector' => (new ReflectionClass($this->config->connector))->getShortName(),
            'Connection' => (new ReflectionClass($this->connection))->getShortName(),
            'Logger' => (new ReflectionClass($this->log))->getShortName(),
        ]);

        return $info;
    }

    /**
     * Deletes keys matching given patterns atomically.
     *
     * @internal
     * @param  string|string[]  $patterns
     * @param  ?string  $group
     * @return void
     */
    protected function deleteByPattern($patterns, ?string $group = null)
    {
        if ($this->config->group_flush === Configuration::GROUP_FLUSH_INCREMENTAL) {
            $this->deleteIncrementallyByPattern($patterns);

            return;
        }

        if (! is_array($patterns)) {
            $patterns = [$patterns];
        }

        $command = $this->config->async_flush ? 'unlink' : 'del';
        $script = file_get_contents(__DIR__ . "/scripts/{$this->config->group_flush}.lua");

        $results = $this->connection->withoutTimeout(function ($connection) use ($script, $patterns, $command) {
            return $connection->eval($script, array_merge($patterns, [$command]), count($patterns));
        });

        $writes = array_sum(array_map(static function ($result) {
            return is_array($result) ? array_sum($result) : $result;
        }, is_array($results) ? $results : [$results]));

        $this->metrics->write($group);
        $this->metrics->storeWrites += (int) --$writes;
    }

    /**
     * Deletes keys matching given patterns non-atomically.
     *
     * @internal
     * @param  string|string[]  $patterns
     * @param  ?string  $group
     * @return void
     */
    protected function deleteIncrementallyByPattern($patterns, ?string $group = null)
    {
        if (! is_array($patterns)) {
            $patterns = [$patterns];
        }

        $command = $this->config->async_flush ? 'unlink' : 'del';

        foreach ($patterns as $pattern) {
            foreach ($this->connection->listKeys($pattern) as $keys) {
                $this->connection->{$command}($keys);

                $this->metrics->write($group);
            }
        }
    }
}
