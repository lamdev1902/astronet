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

use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connections\ConnectionInterface;

interface ConnectorInterface
{
    /**
     * Loads required libraries and throw exception on failure.
     *
     * @return void
     */
    public static function boot(): void; // phpcs:ignore PHPCompatibility

    /**
     * Checks whether the client supports the given feature.
     *
     * @return bool
     */
    public static function supports(string $feature): bool;

    /**
     * Create a new connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\ConnectionInterface
     */
    public static function connect(Configuration $config): ConnectionInterface;

    /**
     * Create a new connection to an instance.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\ConnectionInterface
     */
    public static function connectToInstance(Configuration $config): ConnectionInterface;

    /**
     * Create a new clustered connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\ConnectionInterface
     */
    public static function connectToCluster(Configuration $config): ConnectionInterface;

    /**
     * Create a new Sentinel connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\ConnectionInterface
     */
    public static function connectToSentinels(Configuration $config): ConnectionInterface;

    /**
     * Create a new replicated connection.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @return \RedisCachePro\Connections\ConnectionInterface
     */
    public static function connectToReplicatedServers(Configuration $config): ConnectionInterface;
}
