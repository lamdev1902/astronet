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

use Closure;
use Throwable;
use ReflectionClass;

use RedisCachePro\Clients\ClientInterface;
use RedisCachePro\Loggers\LoggerInterface;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connections\ConnectionInterface;

use RedisCachePro\Exceptions\ObjectCacheException;
use RedisCachePro\Exceptions\InvalidCacheKeyTypeException;

/**
 * @property-read int $hits
 * @property-read int $misses
 * @property-read int $cache_hits
 * @property-read int $cache_misses
 * @property-read array<string> $no_remote_groups
 */
abstract class ObjectCache implements ObjectCacheInterface
{
    /**
     * The configuration instance.
     *
     * @var \RedisCachePro\Configuration\Configuration
     */
    protected $config;

    /**
     * The connection instance.
     *
     * @var ?\RedisCachePro\Connections\ConnectionInterface
     */
    protected $connection;

    /**
     * The logger instance.
     *
     * @var \RedisCachePro\Loggers\LoggerInterface
     */
    protected $log;

    /**
     * Holds the objects cached in runtime memory.
     *
     * @var array<string, array<int|string, mixed>>
     */
    protected $cache = [];

    /**
     * ...
     *
     * @var \RedisCachePro\ObjectCaches\ObjectCacheMetrics
     */
    protected $metrics;

    /**
     * Holds an internal cache copy of the configured prefix.
     *
     * @var ?string
     */
    protected $prefix;

    /**
     * The blog id used as prefix in network environments.
     *
     * @var int
     */
    protected $blogId;

    /**
     * Whether the environment is a network.
     *
     * @var bool
     */
    protected $isMultisite = false;

    /**
     * Holds an internal cache copy of whether the connection is a cluster.
     *
     * @var ?bool
     */
    protected $isCluster;

    /**
     * The list of global cache groups that are not
     * blog specific in a network environment.
     *
     * @var array<string>
     */
    protected $globalGroups = [];

    /**
     * The list of non-persistent groups.
     *
     * @var array<string>
     */
    protected $nonPersistentGroups = [];

    /**
     * The list of non-persistent group matches for fast lookups.
     *
     * @var array<string, bool>
     */
    protected $nonPersistentGroupMatches = [];

    /**
     * The list of non-prefetchable groups.
     *
     * @var array<string>
     */
    protected $nonPrefetchableGroups = [];

    /**
     * The list of non-prefetchable group matches for fast lookups.
     *
     * @var array<string, bool>
     */
    protected $nonPrefetchableGroupMatches = [];

    /**
     * Set up the object cache instance.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  \RedisCachePro\Connections\ConnectionInterface  $connection
     * @param  ?\RedisCachePro\ObjectCaches\ObjectCacheMetrics  $metrics
     * @return void
     */
    protected function setup(
        Configuration $config,
        ?ConnectionInterface $connection = null,
        ?ObjectCacheMetrics $metrics = null
    ) {
        $this->config = $config;
        $this->log = $config->logger;

        $this->prefix = $config->prefix ?: '';
        $this->isCluster = (bool) $config->cluster;

        $this->connection = $connection;

        $this->metrics = $metrics
            ?: new ObjectCacheMetrics($config, $connection);
    }

    /**
     * Returns the configuration instance.
     *
     * @return \RedisCachePro\Configuration\Configuration
     */
    public function config(): Configuration
    {
        return $this->config;
    }

    /**
     * Returns the connection instance.
     *
     * @return \RedisCachePro\Connections\ConnectionInterface|null
     */
    public function connection(): ?ConnectionInterface // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewNullableTypes.returnTypeFound
    {
        return $this->connection;
    }

    /**
     * Returns the connection's client.
     *
     * @internal
     * @return \RedisCachePro\Clients\ClientInterface|null
     */
    public function client(): ?ClientInterface // phpcs:ignore PHPCompatibility.FunctionDeclarations.NewNullableTypes.returnTypeFound
    {
        return $this->connection
            ? $this->connection->client()
            : null;
    }

    /**
     * Returns the client name the object cache is using.
     *
     * @return string
     */
    public function clientName()
    {
        $client = $this->client();

        if (! $client) {
            return 'Unknown';
        }

        return (new ReflectionClass($client))->getShortName();
    }

    /**
     * Returns the logger instance.
     *
     * @return \RedisCachePro\Loggers\LoggerInterface
     */
    public function logger(): LoggerInterface
    {
        return $this->log;
    }

    /**
     * Set given groups as global.
     *
     * @param  array<string>  $groups
     * @return void
     */
    public function add_global_groups(array $groups)
    {
        $this->globalGroups = \array_unique(
            \array_merge($this->globalGroups, \array_values($groups))
        );
    }

    /**
     * Set given groups as non-persistent.
     *
     * @param  array<string>  $groups
     * @return void
     */
    public function add_non_persistent_groups(array $groups)
    {
        $this->nonPersistentGroups = \array_unique(
            \array_merge($this->nonPersistentGroups, \array_values($groups))
        );

        foreach (\array_values($groups) as $group) {
            if (\strpos($group, '*') === false) {
                unset($this->nonPersistentGroupMatches[$group]);
            } else {
                foreach (\array_keys($this->nonPersistentGroupMatches) as $nonPersistentGroupMatch) {
                    if (\fnmatch($group, $nonPersistentGroupMatch)) {
                        unset($this->nonPersistentGroupMatches[$nonPersistentGroupMatch]);
                    }
                }
            }
        }
    }

    /**
     * Set given groups as non-prefetchable.
     *
     * @param  array<string>  $groups
     * @return void
     */
    public function add_non_prefetchable_groups(array $groups)
    {
        $this->nonPrefetchableGroups = \array_unique(
            \array_merge($this->nonPrefetchableGroups, \array_values($groups))
        );
    }

    /**
     * Decrement given value by given offset.
     *
     * Forces value to be a signed integer.
     *
     * @param  int|string  $value
     * @param  int  $offset
     * @return int
     */
    protected function decrement($value, int $offset): int
    {
        if (! \is_int($value)) {
            $value = 0;
        }

        $value -= $offset;

        return max(0, $value);
    }

    /**
     * Returns an array of all global groups.
     *
     * @return array<string>
     */
    public function globalGroups(): array
    {
        return $this->globalGroups;
    }

    /**
     * Returns an array of all non-prefetchable groups.
     *
     * @return array<string>
     */
    public function nonPrefetchableGroups(): array
    {
        return $this->nonPrefetchableGroups;
    }

    /**
     * Returns an array of all non-persistent groups.
     *
     * @return array<string>
     */
    public function nonPersistentGroups(): array
    {
        return $this->nonPersistentGroups;
    }

    /**
     * Increment given value by given offset.
     *
     * Forces value to be a signed integer.
     *
     * @param  int|string  $value
     * @param  int  $offset
     * @return int
     */
    protected function increment($value, int $offset): int
    {
        if (! \is_int($value)) {
            $value = 0;
        }

        $value += $offset;

        return max(0, $value);
    }

    /**
     * Whether the group is a global group.
     *
     * @param  string  $group
     * @return bool
     */
    public function isGlobalGroup(string $group): bool
    {
        return \in_array($group, $this->globalGroups);
    }

    /**
     * Whether the group is persistent.
     *
     * @param  string  $group
     * @return bool
     */
    public function isPersistentGroup(string $group): bool
    {
        if (isset($this->nonPersistentGroupMatches[$group])) {
            return ! $this->nonPersistentGroupMatches[$group];
        }

        return ! $this->isNonPersistentGroup($group);
    }

    /**
     * Whether the group is non-persistent.
     *
     * @param  string  $group
     * @return bool
     */
    public function isNonPersistentGroup(string $group): bool
    {
        if (isset($this->nonPersistentGroupMatches[$group])) {
            return $this->nonPersistentGroupMatches[$group];
        }

        foreach ($this->nonPersistentGroups as $nonPersistentGroup) {
            if (\strpos($nonPersistentGroup, '*') === false) {
                if ($group === $nonPersistentGroup) {
                    return $this->nonPersistentGroupMatches[$group] = true;
                }
            } else {
                if (\fnmatch($nonPersistentGroup, $group)) {
                    return $this->nonPersistentGroupMatches[$group] = true;
                }
            }
        }

        return $this->nonPersistentGroupMatches[$group] = false;
    }

    /**
     * Whether the group is prefetchable.
     *
     * @param  string  $group
     * @return bool
     */
    public function isPrefetchableGroup(string $group): bool
    {
        if (isset($this->nonPrefetchableGroupMatches[$group])) {
            return ! $this->nonPrefetchableGroupMatches[$group];
        }

        return ! $this->isNonPrefetchableGroup($group);
    }

    /**
     * Whether the group is non-prefetchable.
     *
     * @param  string  $group
     * @return bool
     */
    public function isNonPrefetchableGroup(string $group): bool
    {
        if (isset($this->nonPrefetchableGroupMatches[$group])) {
            return $this->nonPrefetchableGroupMatches[$group];
        }

        foreach ($this->nonPrefetchableGroups as $nonPrefetchableGroup) {
            if (\strpos($nonPrefetchableGroup, '*') === false) {
                if ($group === $nonPrefetchableGroup) {
                    return $this->nonPrefetchableGroupMatches[$group] = true;
                }
            } else {
                if (\fnmatch($nonPrefetchableGroup, $group)) {
                    return $this->nonPrefetchableGroupMatches[$group] = true;
                }
            }
        }

        return $this->nonPrefetchableGroupMatches[$group] = false;
    }

    /**
     * Whether the environment is a network.
     *
     * @return bool
     */
    public function isMultisite(): bool
    {
        return (bool) $this->isMultisite;
    }

    /**
     * Returns various information about the object cache.
     *
     * @return \RedisCachePro\Support\ObjectCacheInfo
     */
    public function info()
    {
        global $wp_object_cache_errors;

        $info = (object) [
            'status' => false,
            'groups' => (object) [
                'global' => $this->globalGroups(),
                'non_persistent' => $this->nonPersistentGroups(),
                'non_prefetchable' => $this->nonPrefetchableGroups(),
            ],
            'errors' => empty($wp_object_cache_errors) ? null : $wp_object_cache_errors,
            'meta' => array_filter([
                'Cache' => (new ReflectionClass($this))->getShortName(),
                'Logger' => (new ReflectionClass($this->log))->getShortName(),
            ]),
        ];

        return $info; // @phpstan-ignore-line
    }

    /**
     * Returns metrics about the object cache.
     *
     * @return \RedisCachePro\ObjectCaches\ObjectCacheMetrics
     */
    public function metrics()
    {
        return $this->metrics->compute($this->cache);
    }

    /**
     * Set the blog id.
     *
     * @param  int  $blogId
     * @return void
     */
    public function setBlogId(int $blogId)
    {
        $this->blogId = $blogId;
    }

    /**
     * Set whether the environment is a network.
     *
     * @param  bool  $isMultisite
     * @return void
     */
    public function setMultisite(bool $isMultisite)
    {
        $this->isMultisite = $isMultisite;
    }

    /**
     * Whether the key was cached in runtime memory.
     *
     * @param  string  $id
     * @param  string  $group
     * @return bool
     */
    protected function hasInMemory(string $id, string $group = 'default')
    {
        return isset($this->cache[$group][$id]);
    }

    /**
     * Retrieves the cache contents from the runtime memory cache.
     *
     * @param  string  $id
     * @param  string  $group
     * @return mixed
     */
    protected function getFromMemory(string $id, string $group = 'default')
    {
        if (\is_object($this->cache[$group][$id])) {
            return clone $this->cache[$group][$id];
        }

        return $this->cache[$group][$id];
    }

    /**
     * Stores the data in runtime memory.
     *
     * @param  string  $id
     * @param  mixed  $data
     * @param  string  $group
     * @return void
     */
    protected function storeInMemory(string $id, $data, string $group = 'default')
    {
        $this->cache[$group][$id] = \is_object($data) ? clone $data : $data;
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
        if (! $id = $this->id($key, $group)) {
            return false;
        }

        if (! $this->hasInMemory($id, $group)) {
            return false;
        }

        unset($this->cache[$group][$id]);

        return true;
    }

    /**
     * Alias to flush the in-memory runtime cache.
     *
     * @return bool
     */
    public function flushRuntime(): bool
    {
        return $this->flush_runtime();
    }

    /**
     * Removes all in-memory cache items for a single blog in multisite environments,
     * otherwise defaults to flushing the entire in-memory cache.
     *
     * Unless the `$network_flush` parameter is given this method
     * will default to `network_flush` configuration option.
     *
     * @param  int  $siteId
     * @param  string  $network_flush
     *
     * @return bool
     */
    public function flushBlog(int $siteId, string $network_flush = null): bool
    {
        if (is_null($network_flush)) {
            $network_flush = $this->config->network_flush;
        }

        $originalBlogId = $this->blogId;
        $this->blogId = $siteId;

        if ($network_flush === Configuration::NETWORK_FLUSH_GLOBAL) {
            foreach ($this->globalGroups() as $group) {
                unset($this->cache[$group]);
            }
        }

        $id = $this->id('*', dechex(3405691582));
        $prefix = trim(preg_replace('/:{?cafebabe}?/', '', (string) $id), '*');
        $prefixLength = strlen($prefix);

        foreach ($this->cache as $group => $keys) {
            foreach (array_keys($keys) as $key) {
                if (substr_compare((string) $key, $prefix, 0, $prefixLength) === 0) {
                    unset($this->cache[$group][$key]);
                }
            }
        }

        $this->blogId = $originalBlogId;

        return true;
    }

    /**
     * Whether `flushBlog()` should be called instead of `flush()`.
     *
     * @return bool
     */
    public function shouldFlushBlog(): bool
    {
        return $this->isMultisite
            && in_array($this->config->network_flush, [
                $this->config::NETWORK_FLUSH_SITE,
                $this->config::NETWORK_FLUSH_GLOBAL,
            ]);
    }

    /**
     * Execute the given closure without data mutations on the connection,
     * such as serialization and compression algorithms.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function withoutMutations(callable $callback)
    {
        return $this->connection->withoutMutations(
            $callback instanceof Closure ? $callback->bindTo($this, $this) : $callback
        );
    }

    /**
     * Build cache identifier for given key and group.
     *
     * 1. The configured prefix is added to all identifiers
     * 2. In network environments the `blog_id` is added to the group
     * 3. On clusters the group is used as the hash slot
     *
     * @param  int|string  $key
     * @param  string  $group
     * @return string|false
     */
    protected function id($key, string $group)
    {
        static $cache = [];

        try {
            $cacheKey = $this->isMultisite
                ? "{$this->prefix}:{$this->blogId}:{$group}:{$key}"
                : "{$this->prefix}:{$group}:{$key}";

            if (isset($cache[$cacheKey])) {
                return $cache[$cacheKey];
            }

            if (! \is_string($key) && ! \is_int($key) || \trim((string) $key) === '') {
                throw new InvalidCacheKeyTypeException;
            }
        } catch (Throwable $th) {
            $this->error(InvalidCacheKeyTypeException::forKey($key));

            return false;
        }

        $blogId = '';

        if ($this->isMultisite && ! \in_array($group, $this->globalGroups)) {
            $blogId = "{$this->blogId}:";
        }

        $key = \str_replace(':', '-', (string) $key);
        $group = \str_replace(':', '-', $group);

        $group = $this->isCluster ? "{{$group}}" : $group;

        $id = "{$this->prefix}:{$blogId}{$group}:{$key}";
        $id = \str_replace(' ', '-', $id);
        $id = \trim($id, ':');
        $id = \strtolower($id);

        return $cache[$cacheKey] = $id;
    }

    /**
     * Handles connection errors.
     *
     * When WP_DEBUG is enabled, the exception will be re-thrown,
     * otherwise a critical log entry is emitted.
     *
     * @param  \Throwable  $error
     * @param  array<mixed>  $context
     * @return void
     */
    protected function error(Throwable $error, array $context = []): void // phpcs:ignore PHPCompatibility
    {
        global $wp_object_cache_errors;

        $wp_object_cache_errors[] = $error->getMessage();

        $this->log->error(
            $error->getMessage(),
            \array_merge(['exception' => $error], $context)
        );

        if ($this->config->debug) {
            throw ObjectCacheException::from($error);
        }
    }

    /**
     * Overload generic properties for compatibility.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'hits':
            case 'cache_hits':
                return $this->metrics->hits;
            case 'misses':
            case 'cache_misses':
                return $this->metrics->misses;
            case 'no_remote_groups':
                return $this->nonPersistentGroups;
            default:
                trigger_error(
                    sprintf('Undefined property: %s::$%s', get_called_class(), $name),
                    E_USER_WARNING
                );
        }
    }

    /**
     * Overload generic properties for compatibility.
     *
     * @param  string  $name
     * @return bool
     */
    public function __isset($name)
    {
        return in_array($name, [
            'hits',
            'misses',
            'cache_hits',
            'cache_misses',
            'no_remote_groups',
        ]);
    }
}
