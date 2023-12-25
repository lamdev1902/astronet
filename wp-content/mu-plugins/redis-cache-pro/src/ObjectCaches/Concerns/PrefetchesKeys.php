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

namespace RedisCachePro\ObjectCaches\Concerns;

use Throwable;
use __PHP_Incomplete_Class;

/**
 * When the `prefetch` configuration option is enabled, all persistent keys are stored in
 * a hash of the current HTTP request and retrieved in batches of cache groups early on.
 */
trait PrefetchesKeys
{
    /**
     * Holds the prefetchable keys.
     *
     * @var array<string, array<int|string, bool>>
     */
    protected $prefetch = [];

    /**
     * Whether prefetching occurred.
     *
     * @var bool
     */
    protected $prefetched = false;

    /**
     * If prefetching is enabled and the current HTTP request is prefetchable,
     * retrieve prefetchable keys in batch of cache groups and register
     * a shutdown handler to keep the prefetches current.
     *
     * @return void
     */
    public function prefetch()
    {
        if ($this->prefetched || ! $this->shouldPrefetch()) {
            return;
        }

        $prefetch = $this->get($this->prefetchRequestHash(), 'prefetches', true);

        if (! empty($prefetch)) {
            foreach ($prefetch as $group => $keys) {
                if ($this->isNonPrefetchableGroup((string) $group)) {
                    continue;
                }

                if (\count($keys) > 1) {
                    $this->ensurePrefetchability(
                        $this->get_multiple($keys, (string) $group, true),
                        $group
                    );
                }
            }
        }

        $this->prefetched = true;
    }

    /**
     * Ensure the prefetched items are not incomplete PHP classes.
     *
     * @param  array<mixed>  $items
     * @param  string  $group
     * @return void
     */
    protected function ensurePrefetchability($items, $group)
    {
        foreach ((array) $items as $key => $value) {
            if ($value instanceof __PHP_Incomplete_Class) {
                $this->undoPrefetch((string) $key, $group);

                continue;
            }

            if (is_array($value) || is_object($value)) {
                array_walk_recursive($value, function ($item) use ($key, $group) {
                    if ($item instanceof __PHP_Incomplete_Class) {
                        $this->undoPrefetch((string) $key, $group);
                    }
                });
            }
        }
    }

    /**
     * Remove the prefetched key from memory and log error.
     *
     * @param  string  $key
     * @param  string  $group
     * @return void
     */
    protected function undoPrefetch(string $key, string $group)
    {
        $this->deleteFromMemory($key, $group);
        $this->metrics->prefetches--;

        if ($this->config->debug) {
            \error_log(
                "objectcache.warning: The cache key `{$key}` is incompatible with prefetching" .
                " and the group `{$group}` should be added to the list of non-prefetchable groups." .
                ' For more information see: https://objectcache.pro/docs/configuration-options/#non-prefetchable-groups'
            );
        }
    }

    /**
     * Store the prefetches for the current HTTP request.
     *
     * @return void
     */
    protected function storePrefetches()
    {
        if (! $this->shouldPrefetch()) {
            return;
        }

        $prefetch = \array_map(static function ($group) {
            return \array_keys($group);
        }, $this->prefetch);

        $prefetch = \array_filter($prefetch, function ($group) {
            return $this->isPrefetchableGroup((string) $group);
        }, \ARRAY_FILTER_USE_KEY);

        // don't prefetch `alloptions` when using hashes
        if ($this->config->split_alloptions) {
            foreach ($prefetch['options'] ?? [] as $i => $key) {
                if (\strpos((string) $key, 'alloptions') !== false) {
                    unset($prefetch['options'][$i]);
                }
            }
        }

        if (! empty($prefetch)) {
            $this->set($this->prefetchRequestHash(), $prefetch, 'prefetches');
        }
    }

    /**
     * Deletes all stored prefetches.
     *
     * @return bool
     */
    public function deletePrefetches()
    {
        $this->prefetch = [];

        $pattern = is_null($this->config->cluster)
            ? '*prefetches:*'
            : '*{prefetches}:*';

        try {
            $this->deleteByPattern($pattern, 'prefetches');
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }

        return true;
    }

    /**
     * Determines whether the current request should use prefetching.
     *
     * @return bool
     */
    protected function shouldPrefetch(): bool
    {
        return $this->config->prefetch
            && $this->requestIsPrefetchable();
    }

    /**
     * Determines whether the current HTTP request is prefetchable.
     *
     * @return bool
     */
    protected function requestIsPrefetchable(): bool
    {
        if (
            (defined('\WP_CLI') && constant('\WP_CLI')) ||
            (defined('\REST_REQUEST') && constant('\REST_REQUEST')) ||
            (defined('\XMLRPC_REQUEST') && constant('\XMLRPC_REQUEST'))
        ) {
            return false;
        }

        if (empty($_SERVER['REQUEST_URI'] ?? null)) {
            return false;
        }

        if (! \in_array($_SERVER['REQUEST_METHOD'] ?? null, ['GET', 'HEAD'])) {
            return false;
        }

        return true;
    }

    /**
     * Generates a prefetch identifier for the current HTTP request.
     *
     * @return string
     */
    protected function prefetchRequestHash(): string
    {
        static $key = null;

        if ($key) {
            return $key;
        }

        $components = [
            'method' => $_SERVER['REQUEST_METHOD'],
            'scheme' => $_SERVER['HTTPS']
                ?? $_SERVER['SERVER_PORT']
                ?? $_SERVER['HTTP_X_FORWARDED_PROTO']
                ?? 'http',
            'host' => $_SERVER['HTTP_X_FORWARDED_HOST']
                ?? $_SERVER['HTTP_HOST']
                ?? $_SERVER['SERVER_NAME']
                ?? 'localhost',
            'path' => \urldecode($_SERVER['REQUEST_URI']),
            'query' => \urldecode($_SERVER['QUERY_STRING'] ?? ''),
        ];

        return $key = \md5(\serialize($components));
    }
}
