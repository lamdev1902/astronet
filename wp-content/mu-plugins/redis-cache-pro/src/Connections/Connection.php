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

use Throwable;

use RedisCachePro\Clients\ClientInterface;
use RedisCachePro\Exceptions\ConnectionException;

abstract class Connection
{
    /**
     * The configuration instance.
     *
     * @var \RedisCachePro\Configuration\Configuration
     */
    protected $config;

    /**
     * The logger instance.
     *
     * @var \RedisCachePro\Loggers\LoggerInterface
     */
    protected $log;

    /**
     * The client instance.
     *
     * @var \RedisCachePro\Clients\ClientInterface
     */
    protected $client;

    /**
     * Information about the last, successfully executed command.
     * Using a public property is a bit sloppy, but it's fast.
     *
     * - wait: milliseconds (ms) waited for the datastore to respond
     * - memory: bytes allocated for retrieved result
     *
     * @var ?array{wait: float, memory: int}
     */
    public $lastCommand;

    /**
     * Returns the connection's client.
     *
     * @return \RedisCachePro\Clients\ClientInterface
     */
    public function client(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Run a command against Redis.
     *
     * @param  string  $name
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function command(string $name, array $parameters = [])
    {
        $this->lastCommand = null;

        $debug = $this->config->debug || $this->config->save_commands;

        $method = strtolower($name);
        $command = strtoupper($name);

        $context = [
            'command' => $command,
            'parameters' => $parameters,
        ];

        if ($debug) {
            $context['backtrace'] = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        }

        try {
            $start = microtime(true);
            $memory = memory_get_usage();

            $result = $this->client->{$method}(...$parameters);

            $memory = (memory_get_usage() - $memory) ?: 1;
            $wait = (microtime(true) - $start) * 1000;
        } catch (Throwable $exception) {
            $this->log->error("Failed to execute `{$command}` command", $context + [
                'exception' => $exception,
            ]);

            throw ConnectionException::from($exception);
        }

        $this->lastCommand = [
            'wait' => $wait,
            'memory' => $memory,
        ];

        if ($debug) {
            $ms = \round($wait, 4);

            $this->log->info("Executed `{$command}` command in {$ms}ms", $context + [
                'time' => $wait,
                'memory' => $memory,
                'result' => $result,
            ]);
        }

        return $result;
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
        return $callback($this);
    }

    /**
     * Returns the memoized result from the given command.
     *
     * @param  string  $command
     * @return mixed
     */
    public function memoize($command)
    {
        static $cache;

        $command = \strtolower($command);

        if (! isset($cache[$command])) {
            $cache[$command] = \method_exists($this, $command)
                ? $this->{$command}()
                : $this->command($command);
        }

        return $cache[$command];
    }

    /**
     * Pass other method calls down to the underlying client.
     *
     * @param  string  $method
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }
}
