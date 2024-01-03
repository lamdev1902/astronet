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

namespace RedisCachePro\Extensions\QueryMonitor;

use QM_Backtrace;
use QM_Collector;

use RedisCachePro\Loggers\ArrayLogger;

use RedisCachePro\Connections\Connection;
use RedisCachePro\Connections\Transaction;
use RedisCachePro\Connections\RelayConnection;
use RedisCachePro\Connections\PhpRedisConnection;

use RedisCachePro\ObjectCaches\ObjectCache;
use RedisCachePro\ObjectCaches\RelayObjectCache;
use RedisCachePro\ObjectCaches\PhpRedisObjectCache;

class CommandsCollector extends QM_Collector
{
    /**
     * Holds the ID of the collector.
     *
     * @var string
     */
    public $id = 'cache-commands';

    /**
     * Ignored methods in all object cache classes.
     *
     * @var array<string, bool>
     */
    const IgnoredMethods = [
        'add' => true,
        'add_multiple' => true,
        'set' => true,
        'set_multiple' => true,
        'get' => true,
        'get_multiple' => true,
        'decr' => true,
        'incr' => true,
        'info' => true,
        'flush' => true,
        'write' => true,
        'multiwrite' => true,
        'delete' => true,
        'delete_multiple' => true,
        'replace' => true,
        'getAllOptions' => true,
        'syncAllOptions' => true,
        'deleteAllOptions' => true,
        'withoutMutations' => true,
    ];

    /**
     * Ignored functions.
     *
     * @var array<string, bool>
     */
    const IgnoredFunctions = [
        'wp_cache_add' => true,
        'wp_cache_add_multiple' => true,
        'wp_cache_get' => true,
        'wp_cache_get_multiple' => true,
        'wp_cache_get_last_changed' => true,
        'wp_cache_set' => true,
        'wp_cache_set_multiple' => true,
        'wp_cache_decr' => true,
        'wp_cache_incr' => true,
        'wp_cache_sear' => true,
        'wp_cache_flush' => true,
        'wp_cache_flush_group' => true,
        'wp_cache_delete' => true,
        'wp_cache_delete_multiple' => true,
        'wp_cache_replace' => true,
        'wp_cache_remember' => true,
        'wp_cache_switch_to_blog' => true,
        'wp_cache_add_global_groups' => true,
        'wp_cache_add_non_persistent_groups' => true,
        'set_transient' => true,
        'get_transient' => true,
        'get_site_transient' => true,
        'get_option' => true,
        'get_site_option' => true,
        'get_network_option' => true,
    ];

    /**
     * Returns the collector name.
     *
     * Obsolete since Query Monitor 3.5.0.
     *
     * @return string
     */
    public function name()
    {
        return 'Commands';
    }

    /**
     * Populate the `data` property.
     *
     * @return void
     */
    public function process()
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCache) {
            return;
        }

        $logger = $wp_object_cache->logger();

        if (! $logger instanceof ArrayLogger) {
            $this->data['commands'] = [];

            return;
        }

        $backtraces = defined('\QM_VERSION')
            && version_compare(constant('\QM_VERSION'), '3.8.1', '>=');

        $this->data['commands'] = $this->buildCommands($logger, $backtraces);

        $types = array_unique(array_column($this->data['commands'], 'command'));
        $types = array_map('strtoupper', $types);

        sort($types);

        $this->data['types'] = $types;

        if ($backtraces) {
            $components = array_map(static function ($command) {
                return $command['backtrace']->get_component()->name;
            }, $this->data['commands']);

            $this->data['components'] = array_unique($components);
        }
    }

    /**
     * Builds the array of Redis commands.
     *
     * @param  \RedisCachePro\Loggers\ArrayLogger  $logger
     * @param  bool  $backtraces
     * @return array<int, array<string, mixed>>
     */
    protected function buildCommands(ArrayLogger $logger, bool $backtraces)
    {
        $commands = [];
        $backtraceArgs = $this->buildBacktraceArgs();

        foreach ($logger->messages() as $message) {
            $command = $message['context']['command'] ?? 'UNKNOWN';

            if ($command === 'RAWCOMMAND') {
                $command = array_shift($message['context']['parameters']);

                if (is_array($command)) {
                    $node = $command;
                    $command = array_shift($message['context']['parameters']);
                    array_unshift($message['context']['parameters'], $node);
                }
            }

            $commands[] = [
                'level' => $message['level'],
                'time' => $message['context']['time'] ?? 0,
                'bytes' => $message['context']['memory'] ?? 0,
                'command' => $command,
                'parameters' => $this->formatParameters(
                    $message['context']['parameters'] ?? []
                ),
                'backtrace' => $backtraces
                    ? new QM_Backtrace($backtraceArgs, $message['context']['backtrace'] ?? null)
                    : null,
            ];
        }

        return $commands;
    }

    /**
     * Builds the backtrace arguments for `QM_Backtrace`.
     *
     * @return array<string, mixed>
     */
    protected function buildBacktraceArgs()
    {
        global $wp_object_cache;

        $connection = $wp_object_cache->connection()
            ? get_class($wp_object_cache->connection())
            : null;

        return [
            'ignore_class' => array_filter([ // @phpstan-ignore-line
                Connection::class => true,
                Transaction::class => true,
                RelayConnection::class => true,
                PhpRedisConnection::class => true,
                $connection => true,
            ]),
            'ignore_method' => [
                ObjectCache::class => self::IgnoredMethods,
                RelayObjectCache::class => self::IgnoredMethods,
                PhpRedisObjectCache::class => self::IgnoredMethods,
                get_class($wp_object_cache) => self::IgnoredMethods,
            ],
            'ignore_func' => self::IgnoredFunctions,
        ];
    }

    /**
     * Converts all parameter values to JSON and trims them down to 200 characters or less.
     *
     * @param  array<mixed>  $parameters
     * @return array<mixed>
     */
    protected function formatParameters($parameters)
    {
        $format = static function ($value) {
            return stripslashes((string) json_encode(
                $value,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ));
        };

        return array_map(static function ($parameter) use ($format) {
            $value = is_object($parameter)
                ? get_class($parameter) . $format($parameter)
                : $format($parameter);

            $value = preg_replace('/\s+/', ' ', (string) $value);
            $value = trim($value, '"');

            if (strlen($value) > 200) {
                return substr($value, 0, 200) . '...';
            }

            return $value;
        }, $parameters);
    }
}
