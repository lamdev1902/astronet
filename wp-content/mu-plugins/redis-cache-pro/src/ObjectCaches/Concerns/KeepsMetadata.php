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

use RedisCachePro\Exceptions\ObjectCacheException;

/**
 * Keeps track of object cache metadata, such as the used configuration.
 *
 * If risky configuration options have changed and the `strict` mode is
 * enabled, the cache will be automatically flushed to avoid collisions.
 */
trait KeepsMetadata
{
    /**
     * The stored object cache metadata.
     *
     * @var ?array<string, array<string, mixed>>
     */
    private $metadata;

    /**
     * Boots the metadata component.
     *
     * @return void
     */
    protected function bootMetadata(): void
    {
        try {
            $this->loadMetadata();
            $this->throwIfRiskyConfigurationChanged();
        } catch (Throwable $exception) {
            $this->integrityProtectionFlush($exception->getMessage());
        }

        $this->maybeUpdateMetadata();
    }

    /**
     * Retrieves the stored metadata from the cache.
     *
     * @return void
     */
    private function loadMetadata()
    {
        $json = $this->withoutMutations([$this, 'getMetadata']);

        if (! is_string($json)) {
            throw new ObjectCacheException('Cache metadata not found');
        }

        $metadata = json_decode($json, true);

        if (! is_array($metadata)) {
            throw new ObjectCacheException(sprintf(
                'Unable to decode cache metadata (%s)',
                (json_last_error() !== JSON_ERROR_NONE)
                    ? json_last_error_msg()
                    : gettype($metadata) . ' found'
            ));
        }

        $this->metadata = $metadata;
    }

    /**
     * Saves the current metadata to the cache.
     *
     * @return void
     */
    public function writeMetadata()
    {
        $this->metadata = $this->buildMetadata();

        $this->withoutMutations([$this, 'setMetadata']);
    }

    /**
     * Build the metadata based on the current configuration.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildMetadata(): array
    {
        global $wp_version;

        return [
            'config' => [
                'client' => $this->clientName(),
                'database' => $this->config->database,
                'prefix' => $this->config->prefix,
                'serializer' => $this->config->serializer,
                'compression' => $this->config->compression,
                'prefetch' => $this->config->prefetch,
                'split_alloptions' => $this->config->split_alloptions,
            ],
            'versions' => [
                'wordpress' => $wp_version,
            ],
        ];
    }

    /**
     * Throws an exception if a risky configuration option has changed.
     *
     * @return void
     */
    private function throwIfRiskyConfigurationChanged()
    {
        $storedConfig = $this->metadata['config'] ?? [];
        $currentConfig = $this->buildMetadata()['config'];

        $riskyOptions = [
            'client', // just a precaution (no known issues)
            'database', // avoid loading foreign dataset
            'prefix', // avoid loading foreign dataset
            'split_alloptions', // avoid loading stale `alloptions` data
            'serializer', // mixing serializers will cause critical errors
            'compression', // mixing data compressions will cause critical errors
        ];

        foreach ($riskyOptions as $option) {
            if (! array_key_exists($option, $storedConfig) || $storedConfig[$option] !== $currentConfig[$option]) {
                throw new ObjectCacheException("Risky configuration option `{$option}` changed");
            }
        }
    }

    /**
     * Updates the object cache metadata, if it has changed.
     *
     * @return void
     */
    private function maybeUpdateMetadata()
    {
        $storedConfig = $this->metadata['config'] ?? [];
        $currentConfig = $this->buildMetadata()['config'];

        if (! empty(array_diff_assoc($currentConfig, $storedConfig))) {
            $this->writeMetadata();
        }
    }

    /**
     * Flushes the object cache for integrity protection, if `strict` mode is enabled.
     *
     * @return bool
     */
    private function integrityProtectionFlush(string $message)
    {
        global $wp_object_cache_flushlog;

        $this->metadata = null;

        if (! $this->config->strict) {
            error_log("objectcache.notice: {$message}, skipping integrity protection flush because `strict` mode is disabled");

            return false;
        }

        error_log("objectcache.notice: {$message}, flushing cache for integrity protection...");

        $wp_object_cache_flushlog[] = [
            'type' => 'flush',
            'backtrace' => \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 5),
        ];

        try {
            $this->flush_runtime();

            return $this->connection->flushdb();
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        } finally {
            $this->metrics->flush();
        }
    }

    /**
     * Callback for `withoutMutations()` in `loadMetadata()`.
     * Improves Query Monitor readability.
     *
     * @internal
     * @return string|false
     */
    public function getMetadata()
    {
        return $this->get('meta', 'objectcache');
    }

    /**
     * Callback for `withoutMutations()` in `writeMetadata()`.
     * Avoids `maxttl` by using connection directly.
     * Improves Query Monitor readability.
     *
     * @internal
     * @return void
     */
    public function setMetadata()
    {
        $this->connection->set(
            (string) $this->id('meta', 'objectcache'),
            json_encode($this->metadata)
        );
    }
}
