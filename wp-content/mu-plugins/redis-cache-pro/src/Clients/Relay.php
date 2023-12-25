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

namespace RedisCachePro\Clients;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;

/**
 * @mixin \Relay\Relay
 * @property \Relay\Relay $client
 */
class Relay extends Client
{
    use Concerns\PhpRedisTransactions;

    public const OPT_SERIALIZER = 1;

    public const OPT_PREFIX = 2;

    public const OPT_READ_TIMEOUT = 3;

    public const OPT_SCAN = 4;

    public const OPT_COMPRESSION = 7;

    public const OPT_COMPRESSION_LEVEL = 9;

    public const OPT_REPLY_LITERAL = 8;

    public const OPT_NULL_MULTIBULK_AS_NULL = 10;

    public const OPT_MAX_RETRIES = 11;

    public const OPT_BACKOFF_ALGORITHM = 12;

    public const OPT_BACKOFF_BASE = 13;

    public const OPT_BACKOFF_CAP = 14;

    public const OPT_PHPREDIS_COMPATIBILITY = 100;

    public const OPT_CLIENT_INVALIDATIONS = 101;

    public const OPT_ALLOW_PATTERNS = 102;

    public const OPT_IGNORE_PATTERNS = 103;

    public const ATOMIC = 0;

    public const PIPELINE = 1;

    public const MULTI = 2;

    public const COMPRESSION_NONE = 0;

    public const COMPRESSION_LZF = 1;

    public const COMPRESSION_ZSTD = 2;

    public const COMPRESSION_LZ4 = 3;

    public const SERIALIZER_NONE = 0;

    public const SERIALIZER_PHP = 1;

    public const SERIALIZER_IGBINARY = 2;

    public const SERIALIZER_MSGPACK = 3;

    public const SERIALIZER_JSON = 4;

    public const BACKOFF_ALGORITHM_DEFAULT = 0;

    public const BACKOFF_ALGORITHM_DECORRELATED_JITTER = 1;

    public const BACKOFF_ALGORITHM_FULL_JITTER = 2;

    public const SCAN_NORETRY = 0;

    public const SCAN_RETRY = 1;

    public const SCAN_PREFIX = 2;

    public const SCAN_NOPREFIX = 3;

    public const RELAY_SCAN_NORETRY = 0;

    public const RELAY_SCAN_RETRY = 1;

    public const RELAY_SCAN_PREFIX = 2;

    public const RELAY_SCAN_NOPREFIX = 3;

    /**
     * Creates an OpenTelemetry tracer from given tracer provider.
     *
     * @param  TracerProviderInterface  $tracerProvider
     * @return TracerInterface
     */
    protected function createOpenTelemetryTracer(TracerProviderInterface $tracerProvider): TracerInterface
    {
        return $tracerProvider->getTracer('Relay', (string) \phpversion('relay'));
    }

    /**
     * Returns the number of bytes allocated, or `0` in client-only mode.
     *
     * @return int
     */
    public function maxMemory()
    {
        if (\method_exists($this->client, 'maxMemory')) {
            return $this->client->maxMemory(); // >= v0.5.0
        }

        if (\method_exists($this->client, 'memory')) {
            return $this->client->memory(); // v0.4.6
        }

        return (int) \ini_get('relay.maxmemory'); // <= 0.4.5
    }

    /**
     * Pass `scan()` calls to the client with iterator reference.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return mixed
     */
    public function scan(&$iterator, $match = null, $count = 0)
    {
        return $this->{$this->callback}(function () use (&$iterator, $match, $count) {
            return $this->client->scan($iterator, $match, $count);
        }, 'scan');
    }

    /**
     * Pass `hscan()` calls to the client with iterator reference.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return mixed
     */
    public function hscan($key, &$iterator, $match = null, $count = 0)
    {
        return $this->{$this->callback}(function () use ($key, &$iterator, $match, $count) {
            return $this->client->hscan($key, $iterator, $match, $count);
        }, 'hscan');
    }

    /**
     * Pass `sscan()` calls to the client with iterator reference.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return mixed
     */
    public function sscan($key, &$iterator, $match = null, $count = 0)
    {
        return $this->{$this->callback}(function () use ($key, &$iterator, $match, $count) {
            return $this->client->sscan($key, $iterator, $match, $count);
        }, 'sscan');
    }

    /**
     * Pass `zscan()` calls to the client with iterator reference.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return mixed
     */
    public function zscan($key, &$iterator, $match = null, $count = 0)
    {
        return $this->{$this->callback}(function () use ($key, &$iterator, $match, $count) {
            return $this->client->zscan($key, $iterator, $match, $count);
        }, 'zscan');
    }
}
