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

/**
 * When the `split_alloptions` configuration option is enabled, the `alloptions` cache key is stored
 * in a Redis hash, instead of a single key. For some setups this helps to reduce data transfer
 * and will minimize race conditions when several processes update options simultaneously.
 */
trait SplitsAllOptionsIntoHash
{
    /**
     * Returns `true` when `alloptions` splitting is enabled
     * and the given `$id` is the `alloptions` cache key.
     *
     * @param  string  $id
     * @return bool
     */
    protected function isAllOptionsId(string $id): bool
    {
        if (! $this->config->split_alloptions) {
            return false;
        }

        return $id === $this->id('alloptions', 'options');
    }

    /**
     * Returns a single `alloptions` array from the Redis hash.
     *
     * @param  string  $id
     * @return array<mixed>|false
     */
    protected function getAllOptions(string $id)
    {
        $alloptions = $this->connection->hgetall("{$id}:hash");

        return empty($alloptions) ? false : $alloptions;
    }

    /**
     * Keeps the `alloptions` Redis hash in sync.
     *
     * 1. All keys present in memory, but not in given data, will be deleted
     * 2. All keys present in data, but not in memory, or with a different value will be set
     *
     * @param  string  $id
     * @param  mixed  $data
     * @return bool
     */
    protected function syncAllOptions(string $id, $data): bool
    {
        $runtimeCache = $this->hasInMemory($id, 'options')
            ? $this->getFromMemory($id, 'options')
            : [];

        $removedOptions = array_keys(array_diff_key($runtimeCache, $data));

        if (! empty($removedOptions)) {
            $this->connection->hdel("{$id}:hash", ...$removedOptions);

            $this->metrics->write('options');
        }

        $changedOptions = array_diff_assoc($data, $runtimeCache);

        if (! empty($changedOptions)) {
            $this->connection->hmset("{$id}:hash", $changedOptions);

            $this->metrics->write('options');
        }

        $this->storeInMemory($id, $data, 'options');

        return true;
    }

    /**
     * Deletes the `alloptions` hash.
     *
     * @param  string  $id
     * @return bool
     */
    protected function deleteAllOptions(string $id): bool
    {
        $command = $this->config->async_flush ? 'unlink' : 'del';
        $result = (bool) $this->connection->{$command}("{$id}:hash");

        $this->metrics->write('options');

        return $result;
    }
}
