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
use Throwable;

use RedisCachePro\Clients\PhpRedis;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connectors\PhpRedisConnector;
use RedisCachePro\Exceptions\ConnectionException;

/**
 * @mixin \RedisCachePro\Clients\PhpRedis
 */
class PhpRedisConnection extends Connection implements ConnectionInterface
{
    /**
     * The Redis instance.
     *
     * @var \RedisCachePro\Clients\PhpRedis
     */
    protected $client;

    /**
     * Create a new PhpRedis instance connection.
     *
     * @param  \RedisCachePro\Clients\PhpRedis  $client
     * @param  \RedisCachePro\Configuration\Configuration  $config
     */
    public function __construct(PhpRedis $client, Configuration $config)
    {
        $this->client = $client;
        $this->config = $config;

        $this->log = $this->config->logger;

        $this->setSerializer();
        $this->setCompression();

        if (PhpRedisConnector::supports('backoff')) {
            $this->setBackoff();
        }
    }

    /**
     * Set the connection's timeout.
     *
     * @param  mixed  $timeout
     * @return void
     */
    protected function setTimeout($timeout)
    {
        $this->client->setOption($this->client::OPT_READ_TIMEOUT, (string) $timeout);
    }

    /**
     * Set the connection's retries and backoff algorithm.
     *
     * @see https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/
     * @return void
     */
    protected function setBackoff()
    {
        if ($this->config->retries) {
            $this->client->setOption($this->client::OPT_MAX_RETRIES, $this->config->retries);
        }

        if ($this->config->backoff === Configuration::BACKOFF_SMART) {
            $this->client->setOption($this->client::OPT_BACKOFF_ALGORITHM, $this->client::BACKOFF_ALGORITHM_DECORRELATED_JITTER);
            $this->client->setOption($this->client::OPT_BACKOFF_BASE, $this->config->retry_interval);
            $this->client->setOption($this->client::OPT_BACKOFF_CAP, \intval($this->config->read_timeout * 1000));
        }
    }

    /**
     * Set the connection's serializer.
     *
     * @return void
     */
    protected function setSerializer()
    {
        if ($this->config->serializer === Configuration::SERIALIZER_PHP) {
            $this->client->setOption($this->client::OPT_SERIALIZER, (string) $this->client::SERIALIZER_PHP);
        }

        if ($this->config->serializer === Configuration::SERIALIZER_IGBINARY) {
            $this->client->setOption($this->client::OPT_SERIALIZER, (string) $this->client::SERIALIZER_IGBINARY);
        }
    }

    /**
     * Unset the connection's serializer.
     *
     * @return void
     */
    protected function unsetSerializer()
    {
        $this->client->setOption($this->client::OPT_SERIALIZER, (string) $this->client::SERIALIZER_NONE);
    }

    /**
     * Set the connection's compression algorithm.
     *
     * @return void
     */
    protected function setCompression()
    {
        if ($this->config->compression === Configuration::COMPRESSION_NONE) {
            $this->client->setOption($this->client::OPT_COMPRESSION, (string) $this->client::COMPRESSION_NONE);
        }

        if ($this->config->compression === Configuration::COMPRESSION_LZF) {
            $this->client->setOption($this->client::OPT_COMPRESSION, (string) $this->client::COMPRESSION_LZF);
        }

        if ($this->config->compression === Configuration::COMPRESSION_ZSTD) {
            $this->client->setOption($this->client::OPT_COMPRESSION, (string) $this->client::COMPRESSION_ZSTD);
            $this->client->setOption($this->client::OPT_COMPRESSION_LEVEL, (string) -5);
        }

        if ($this->config->compression === Configuration::COMPRESSION_LZ4) {
            $this->client->setOption($this->client::OPT_COMPRESSION, (string) $this->client::COMPRESSION_LZ4);
        }
    }

    /**
     * Set the connection's compression algorithm.
     *
     * @return void
     */
    protected function unsetCompression()
    {
        $this->client->setOption($this->client::OPT_COMPRESSION, (string) $this->client::COMPRESSION_NONE);
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
        $this->unsetSerializer();
        $this->unsetCompression();

        try {
            return $callback($this);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $this->setSerializer();
            $this->setCompression();
        }
    }

    /**
     * Execute callback without read timeout.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function withoutTimeout(callable $callback)
    {
        return $this->withTimeout($callback, -1);
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
        $this->setTimeout((string) $timeout);

        try {
            return $callback($this);
        } catch (Throwable $exception) {
            throw $exception;
        } finally {
            $this->setTimeout((string) $this->config->read_timeout);
        }
    }

    /**
     * Flush the selected Redis database.
     *
     * When asynchronous flushing is not used the connection’s read timeout (if present)
     * is disabled to avoid a timeout and restores the timeout afterwards,
     * even in the event of an exception.
     *
     * @param  bool|null  $async
     * @return bool
     */
    public function flushdb($async = null)
    {
        if ($async ?? $this->config->async_flush) {
            $asyncValue = \version_compare((string) \phpversion('redis'), '6.0', '<')
                ? true // PhpRedis 4.x - 5.x
                : false; // PhpRedis 6.x

            return $this->command('flushdb', [$asyncValue]);
        }

        return $this->withoutTimeout(function () {
            return $this->command('flushdb');
        });
    }

    /**
     * Hijack `pipeline()` calls to allow command logging.
     *
     * @return \RedisCachePro\Connections\Transaction
     */
    public function pipeline()
    {
        return Transaction::pipeline($this);
    }

    /**
     * Hijack `multi()` calls to allow command logging.
     *
     * @param  int  $type
     * @return \RedisCachePro\Connections\Transaction
     */
    public function multi(int $type = null)
    {
        return $type === $this->client::PIPELINE
            ? Transaction::multi($this)
            : Transaction::pipeline($this);
    }

    /**
     * Send `scan()` calls directly to the client to make passed-by-reference iterator work.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function scan(&$iterator, $match = null, $count = 0)
    {
        return $this->client->scan($iterator, $match, $count);
    }

    /**
     * Send `hscan()` calls directly to the client to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function hscan($key, &$iterator, $match = null, int $count = 0)
    {
        return $this->client->hscan($key, $iterator, $match, $count);
    }

    /**
     * Send `sscan()` calls directly to the client to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function sscan($key, &$iterator, $match = null, int $count = 0)
    {
        return $this->client->sscan($key, $iterator, $match, $count);
    }

    /**
     * Send `zscan()` calls directly to the client to make passed-by-reference iterator work.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<string>|false
     */
    public function zscan($key, &$iterator, $match = null, int $count = 0)
    {
        return $this->client->zscan($key, $iterator, $match, $count);
    }

    /**
     * Yields all keys matching the given pattern.
     *
     * @param  string|null  $pattern
     * @return \Generator<array<int, mixed>>
     */
    public function listKeys(?string $pattern = null): Generator
    {
        $iterator = null;

        do {
            $keys = $this->client->scan($iterator, $pattern, 500);

            if (! empty($keys)) {
                yield $keys;
            }
        } while ($iterator > 0);
    }

    /**
     * Execute hijacked MULTI transaction/pipeline.
     *
     * This mimics `Connection::command()`.
     *
     * @param  \RedisCachePro\Connections\Transaction  $tx
     * @return array<mixed>
     */
    public function commands(Transaction $tx)
    {
        $this->lastCommand = null;

        $debug = $this->config->debug || $this->config->save_commands;

        $method = $tx->type;
        $context = [
            'command' => \strtoupper($method),
            'parameters' => [],
        ];

        if ($debug) {
            $context['backtrace'] = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        try {
            $start = microtime(true);

            $pipe = $this->client->{$method}();

            foreach ($tx->commands as $command) {
                $pipe->{$command[0]}(...$command[1]);

                $context['parameters'][] = \array_merge([\strtoupper($command[0])], $command[1]);
            }

            $memory = memory_get_usage();

            $results = $pipe->exec();

            $memory = memory_get_usage() - $memory;
            $wait = (microtime(true) - $start) * 1000;
        } catch (Throwable $exception) {
            $this->log->error('Failed to execute transaction', $context + [
                'exception' => $exception,
            ]);

            throw ConnectionException::from($exception);
        }

        if (! is_array($results)) {
            $type = gettype($results);

            throw new ConnectionException("Transaction returned an unexpected type ({$type})");
        }

        $resultsCount = count($results);
        $commandCount = count($tx->commands);

        if ($resultsCount !== $commandCount) {
            throw new ConnectionException("Transaction returned {$resultsCount} results but unexpected {$commandCount}");
        }

        $this->lastCommand = [
            'wait' => $wait,
            'memory' => $memory,
        ];

        if ($debug) {
            $ms = \round($wait, 4);

            $this->log->info("Executed transaction in {$ms}ms", $context + [
                'time' => $wait,
                'memory' => $memory,
                'result' => $results,
            ]);
        }

        return $results;
    }
}
