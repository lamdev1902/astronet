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

namespace RedisCachePro\Connectors;

use LogicException;

use Redis;
use RedisException;

use RedisCluster;
use RedisClusterException;

use RedisCachePro\Clients\PhpRedis;
use RedisCachePro\Clients\PhpRedisCluster;

use RedisCachePro\Configuration\Configuration;

use RedisCachePro\Connections\PhpRedisConnection;
use RedisCachePro\Connections\ConnectionInterface;
use RedisCachePro\Connections\PhpRedisClusterConnection;
use RedisCachePro\Connections\PhpRedisSentinelsConnection;
use RedisCachePro\Connections\PhpRedisReplicatedConnection;

use RedisCachePro\Exceptions\InvalidDatabaseException;
use RedisCachePro\Exceptions\PhpRedisMissingException;
use RedisCachePro\Exceptions\PhpRedisOutdatedException;
use RedisCachePro\Exceptions\ConfigurationInvalidException;

class PhpRedisConnector implements ConnectorInterface
{
    use Concerns\HandlesBackoff;

    /**
     * The minimum required PhpRedis version.
     *
     * @var string
     */
    const RequiredVersion = '3.1.1';

    /**
     * Ensure PhpRedis v3.1.1 or newer loaded.
     *
     * @return void
     */
    public static function boot(): void // phpcs:ignore PHPCompatibility
    {
        if (! \extension_loaded('redis')) {
            throw new PhpRedisMissingException;
        }

        if (\version_compare((string) \phpversion('redis'), self::RequiredVersion, '<')) {
            throw new PhpRedisOutdatedException;
        }
    }

    /**
     * Check whether the client supports the given feature.
     *
     * @return bool
     */
    public static function supports(string $feature): bool
    {
        switch ($feature) {
            case Configuration::SERIALIZER_PHP:
                return \defined('\Redis::SERIALIZER_PHP');
            case Configuration::SERIALIZER_IGBINARY:
                return \defined('\Redis::SERIALIZER_IGBINARY');
            case Configuration::COMPRESSION_NONE:
                return true;
            case Configuration::COMPRESSION_LZF:
                return \defined('\Redis::COMPRESSION_LZF');
            case Configuration::COMPRESSION_LZ4:
                return \defined('\Redis::COMPRESSION_LZ4');
            case Configuration::COMPRESSION_ZSTD:
                return \defined('\Redis::COMPRESSION_ZSTD');
            case 'retries':
            case 'backoff':
                return \defined('\Redis::OPT_MAX_RETRIES')
                    && \defined('\Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER');
            case 'tls':
                return \version_compare((string) \phpversion('redis'), '5.3.2', '>=');
        }

        return false;
    }

    /**
     * Create a new PhpRedis connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\ConnectionInterface
     */
    public static function connect(Configuration $config): ConnectionInterface
    {
        if ($config->cluster) {
            return static::connectToCluster($config);
        }

        if ($config->sentinels) {
            return static::connectToSentinels($config);
        }

        if ($config->servers) {
            return static::connectToReplicatedServers($config);
        }

        return static::connectToInstance($config);
    }

    /**
     * Create a new PhpRedis connection to an instance.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\PhpRedisConnection
     */
    public static function connectToInstance(Configuration $config): ConnectionInterface
    {
        $client = new PhpRedis(function () {
            return new Redis;
        }, $config->tracer);

        $version = (string) \phpversion('redis');

        $persistent = $config->persistent;
        $persistentId = '';

        $host = $config->host;

        if (\version_compare($version, '5.0.0', '>=') && $config->scheme) {
            $host = "{$config->scheme}://{$config->host}";
        }

        $host = \str_replace('unix://', '', $host);

        $method = $persistent ? 'pconnect' : 'connect';

        $arguments = [
            $host,
            $config->port ?? 0,
            $config->timeout,
            $persistentId,
            $config->retry_interval,
        ];

        if (\version_compare($version, '3.1.3', '>=')) {
            $arguments[] = $config->read_timeout;
        }

        if ($config->tls_options && \version_compare($version, '5.3.0', '>=')) {
            $arguments[] = ['stream' => $config->tls_options];
        }

        $retries = 0;

        CONNECTION_RETRY: {
            $delay = self::nextDelay($config, $retries);

            try {
                $client->{$method}(...$arguments);
            } catch (RedisException $exception) {
                if (++$retries >= $config->retries) {
                    throw $exception;
                }

                \usleep($delay * 1000);
                goto CONNECTION_RETRY;
            }
        }

        if ($config->username && $config->password) {
            $client->auth([$config->username, $config->password]);
        } elseif ($config->password) {
            $client->auth($config->password);
        }

        if ($config->database) {
            if (! $client->select($config->database)) {
                throw new InvalidDatabaseException((string) $config->database);
            }
        }

        if ($config->read_timeout) {
            $client->setOption($client::OPT_READ_TIMEOUT, (string) $config->read_timeout);
        }

        return new PhpRedisConnection($client, $config);
    }

    /**
     * Create a new clustered PhpRedis connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\PhpRedisClusterConnection
     */
    public static function connectToCluster(Configuration $config): ConnectionInterface
    {
        if (\is_string($config->cluster)) {
            $arguments = [$config->cluster];
        } else {
            $arguments = [
                null,
                \array_values($config->cluster),
                $config->timeout,
                $config->read_timeout,
                $config->persistent,
            ];

            $version = (string) \phpversion('redis');

            if (\version_compare($version, '4.3.0', '>=')) {
                $arguments[] = $config->password ?? '';
            }

            if ($config->tls_options && \version_compare($version, '5.3.2', '>=')) {
                $arguments[] = $config->tls_options;
            }
        }

        $client = null;
        $retries = 0;

        CLUSTER_RETRY: {
            $delay = self::nextDelay($config, $retries);

            try {
                $client = new PhpRedisCluster(function () use ($arguments) {
                    return new RedisCluster(...$arguments);
                }, $config->tracer);
            } catch (RedisClusterException $exception) {
                if (++$retries >= $config->retries) {
                    throw $exception;
                }

                \usleep($delay * 1000);
                goto CLUSTER_RETRY;
            }
        }

        if ($config->cluster_failover) {
            $client->setOption($client::OPT_SLAVE_FAILOVER, $config->getClusterFailover());
        }

        return new PhpRedisClusterConnection($client, $config);
    }

    /**
     * Create a new PhpRedis Sentinels connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\PhpRedisSentinelsConnection
     */
    public static function connectToSentinels(Configuration $config): ConnectionInterface
    {
        if (version_compare((string) phpversion('redis'), '5.3.2', '<')) {
            throw new LogicException('Redis Sentinel requires PhpRedis v5.3.2 or newer');
        }

        if (! $config->service) {
            throw new ConfigurationInvalidException('Missing `service` configuration option');
        }

        return new PhpRedisSentinelsConnection($config);
    }

    /**
     * Create a new replicated PhpRedis connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\PhpRedisReplicatedConnection
     */
    public static function connectToReplicatedServers(Configuration $config): ConnectionInterface
    {
        $replicas = [];

        foreach ($config->servers as $server) {
            $serverConfig = clone $config;
            $serverConfig->setUrl($server);
            $role = Configuration::parseUrl($server)['role'];

            if (in_array($role, ['primary', 'master'])) {
                $primary = static::connectToInstance($serverConfig);
            } else {
                $replicas[] = static::connectToInstance($serverConfig);
            }
        }

        if (! isset($primary)) {
            throw new LogicException('No primary replication node found');
        }

        return new PhpRedisReplicatedConnection($primary, $replicas, $config);
    }
}
