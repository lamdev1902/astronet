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

use Relay\Relay as RelayClient;
use Relay\Exception as RelayException;

use RedisCachePro\Clients\Relay;

use RedisCachePro\Configuration\Configuration;

use RedisCachePro\Connections\RelayConnection;
use RedisCachePro\Connections\ConnectionInterface;
use RedisCachePro\Connections\RelaySentinelsConnection;
use RedisCachePro\Connections\RelayReplicatedConnection;

use RedisCachePro\Exceptions\RelayMissingException;
use RedisCachePro\Exceptions\RelayOutdatedException;
use RedisCachePro\Exceptions\InvalidDatabaseException;
use RedisCachePro\Exceptions\ConfigurationInvalidException;

class RelayConnector implements ConnectorInterface
{
    use Concerns\HandlesBackoff;

    /**
     * The minimum required Relay version.
     *
     * @var string
     */
    const RequiredVersion = '0.4.0-dev';

    /**
     * Ensure the minimum required Relay version is loaded.
     *
     * @return void
     */
    public static function boot(): void // phpcs:ignore PHPCompatibility
    {
        if (! \extension_loaded('relay')) {
            throw new RelayMissingException;
        }

        if (\version_compare((string) \phpversion('relay'), self::RequiredVersion, '<')) {
            throw new RelayOutdatedException;
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
                return \defined('\Relay\Relay::SERIALIZER_PHP');
            case Configuration::SERIALIZER_IGBINARY:
                return \defined('\Relay\Relay::SERIALIZER_IGBINARY');
            case Configuration::COMPRESSION_NONE:
                return true;
            case Configuration::COMPRESSION_LZF:
                return \defined('\Relay\Relay::COMPRESSION_LZF');
            case Configuration::COMPRESSION_LZ4:
                return \defined('\Relay\Relay::COMPRESSION_LZ4');
            case Configuration::COMPRESSION_ZSTD:
                return \defined('\Relay\Relay::COMPRESSION_ZSTD');
            case 'retries':
            case 'backoff':
                return \defined('\Relay\Relay::OPT_MAX_RETRIES')
                    && \defined('\Relay\Relay::BACKOFF_ALGORITHM_DECORRELATED_JITTER');
            case 'tls':
                return true;
            case 'allow-patterns':
                return \defined('\Relay\Relay::OPT_ALLOW_PATTERNS');
        }

        return false;
    }

    /**
     * Create a new Relay connection.
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
     * Create a new Relay connection to an instance.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\RelayConnection
     */
    public static function connectToInstance(Configuration $config): ConnectionInterface
    {
        $client = new Relay(function () {
            return new RelayClient;
        }, $config->tracer);

        $persistent = $config->persistent;
        $persistentId = '';

        $host = $config->host;

        if ($config->scheme) {
            $host = "{$config->scheme}://{$config->host}";
        }

        $host = \str_replace('unix://', '', $host);

        $method = $persistent ? 'pconnect' : 'connect';

        $context = [];

        if ($config->tls_options) {
            $context['stream'] = $config->tls_options;
        }

        if (! $config->relay->cache) {
            $context['use-cache'] = false;
        }

        $arguments = [
            $host,
            $config->port ?? 0,
            $config->timeout,
            $persistentId,
            $config->retry_interval,
            0, // set later using `setOption()`
            $context,
        ];

        $retries = 0;

        CONNECTION_RETRY: {
            $delay = self::nextDelay($config, $retries);

            try {
                $client->{$method}(...$arguments);
            } catch (RelayException $exception) {
                if (++$retries >= $config->retries) {
                    throw $exception;
                }

                \usleep($delay * 1000);
                goto CONNECTION_RETRY;
            }
        }

        // set read-timeout as option to avoid confusing hiredis error message
        $client->setOption(RelayClient::OPT_READ_TIMEOUT, $config->read_timeout);

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

        return new RelayConnection($client, $config);
    }

    /**
     * Create a new clustered Relay connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     *
     * @throws \LogicException
     */
    public static function connectToCluster(Configuration $config): ConnectionInterface
    {
        throw new LogicException('Relay does not yet support Redis Cluster');
    }

    /**
     * Create a new Relay Sentinels connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\RelaySentinelsConnection
     */
    public static function connectToSentinels(Configuration $config): ConnectionInterface
    {
        if (version_compare((string) phpversion('relay'), '0.5.0-dev', '<')) {
            throw new LogicException('Relay Sentinel requires Relay v0.5.0 or newer');
        }

        if (! $config->service) {
            throw new ConfigurationInvalidException('Missing `service` configuration option');
        }

        return new RelaySentinelsConnection($config);
    }

    /**
     * Create a new replicated Relay connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\RelayReplicatedConnection
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

        return new RelayReplicatedConnection($primary, $replicas, $config);
    }
}
