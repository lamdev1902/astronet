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

use RedisCachePro\Clients\ClientInterface;

/**
 * @mixin \Redis
 */
interface ConnectionInterface
{
    /**
     * Returns the connection's client.
     *
     * @return \RedisCachePro\Clients\ClientInterface
     */
    public function client(): ClientInterface;

    /**
     * Run a command against Redis.
     *
     * @param  string  $name
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function command(string $name, array $parameters = []);

    /**
     * Execute transaction.
     *
     * @param  \RedisCachePro\Connections\Transaction  $tx
     * @return array<mixed>
     */
    public function commands(Transaction $tx);

    /**
     * Returns the memoized result from the given command.
     *
     * @param  string  $command
     * @return mixed
     */
    public function memoize($command);

    /**
     * Execute the callback without data mutations on the connection,
     * such as serialization and compression algorithms.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function withoutMutations(callable $callback);
}
