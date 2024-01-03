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

/**
 * @mixin \Redis
 */
final class Transaction
{
    /**
     * The string representing a pipeline transaction.
     *
     * @var string
     */
    const Pipeline = 'pipeline';

    /**
     * The string representing a multi transaction.
     *
     * @var string
     */
    const Multi = 'multi';

    /**
     * The transaction type.
     *
     * @var string
     */
    public $type;

    /**
     * The underlying connection to execute the transaction on.
     *
     * @var \RedisCachePro\Connections\ConnectionInterface
     */
    public $connection;

    /**
     * Holds all queued commands.
     *
     * @var array<mixed>
     */
    public $commands = [];

    /**
     * Creates a new transaction instance.
     *
     * @param  string  $type
     * @param  \RedisCachePro\Connections\ConnectionInterface  $connection
     * @return void
     */
    public function __construct(string $type, ConnectionInterface $connection)
    {
        $this->type = $type;
        $this->connection = $connection;
    }

    /**
     * Creates a new pipeline transaction.
     *
     * @param  \RedisCachePro\Connections\ConnectionInterface  $connection
     * @return self
     */
    public static function pipeline(ConnectionInterface $connection)
    {
        return new static(static::Pipeline, $connection);
    }

    /**
     * Creates a new multi transaction.
     *
     * @param  \RedisCachePro\Connections\ConnectionInterface  $connection
     * @return self
     */
    public static function multi(ConnectionInterface $connection)
    {
        return new static(static::Multi, $connection);
    }

    /**
     * Shim to execute the transaction on the underlying connection.
     *
     * @return array<mixed>
     */
    public function exec()
    {
        return $this->connection->commands($this);
    }

    /**
     * Memorize all method calls for later execution.
     *
     * @param  string  $method
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $this->commands[] = [$method, $arguments];

        return $this;
    }
}
