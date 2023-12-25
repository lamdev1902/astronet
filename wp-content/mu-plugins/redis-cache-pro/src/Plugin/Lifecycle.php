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

namespace RedisCachePro\Plugin;

use Exception;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Lifecycle
{
    /**
     * Boot lifecycle component and register hooks.
     *
     * @return void
     */
    public function bootLifecycle()
    {
        add_action('init', [$this, 'run']);

        add_action("deactivate_{$this->basename}", [$this, 'deactivate']);
        add_action("uninstall_{$this->basename}", [$this, 'uninstall']);

        add_filter('pre_objectcache_flush', [$this, 'maybeLogFlush'], PHP_INT_MAX);
        add_filter('pre_objectcache_flush_group', [$this, 'maybeLogGroupFlush'], PHP_INT_MAX, 2);
    }

    /**
     * Called when initializing WordPress.
     *
     * @return void
     */
    public function run()
    {
        $this->maybeLogEarlyFlushes();

        if (is_admin()) {
            $this->license();
        }
    }

    /**
     * Called by `deactivate_{$plugin}` hook.
     *
     * @return void
     */
    public function deactivate()
    {
        delete_site_option('objectcache_license');

        $this->disableDropin();
    }

    /**
     * Called by `uninstall_{$plugin}` hook.
     *
     * @return void
     */
    public function uninstall()
    {
        delete_site_option('objectcache_options');

        delete_site_option('objectcache_license');
        delete_site_option('objectcache_relay_license');

        delete_site_option('objectcache_flushlog');
        delete_site_option('objectcache_flushlog_groups');

        wp_unschedule_event(
            (int) wp_next_scheduled('objectcache_prune_analytics'),
            'objectcache_prune_analytics'
        );
    }

    /**
     * Attempt to wipe the Redis database on a standalone connection.
     *
     * Exceptions are caught and converted to error log messages.
     *
     * @return bool
     */
    public function resetCache()
    {
        try {
            $this->logFlush(null, 2);

            return $this->config->connector::connect($this->config)->flushdb();
        } catch (Exception $exception) {
            error_log($exception->getMessage());

            return false;
        }
    }

    /**
     * Maybe log cache flush. Called by `pre_objectcache_flush` hook.
     *
     * @param  bool  $should_flush
     * @return bool
     */
    public function maybeLogFlush($should_flush)
    {
        if ($should_flush) {
            $this->logFlush();
        }

        return $should_flush;
    }

    /**
     * Maybe log cache flush. Called by `pre_objectcache_flush_group` hook.
     *
     * @param  bool  $should_flush
     * @param  string  $group
     * @return bool
     */
    public function maybeLogGroupFlush($should_flush, $group)
    {
        if ($should_flush) {
            $this->logGroupFlush($group);
        }

        return $should_flush;
    }

    /**
     * Log early cache and cache group flushes, if any occurred.
     *
     * @return void
     */
    protected function maybeLogEarlyFlushes()
    {
        global $wp_object_cache_flushlog;

        if (! is_array($wp_object_cache_flushlog)) {
            return;
        }

        foreach ($wp_object_cache_flushlog as $flush) {
            if (! is_array($flush)) {
                continue;
            }

            if ($flush['type'] === 'flush') {
                $this->logFlush($flush['backtrace']);
            }

            if ($flush['type'] === 'group-flush') {
                $this->logGroupFlush($flush['group'], $flush['backtrace']);
            }
        }
    }

    /**
     * Log cache flush.
     *
     * @param  ?array<int, array<string, mixed>>  $backtrace
     * @param  int  $skip_frames
     * @return void
     */
    public function logFlush($backtrace = null, int $skip_frames = 1)
    {
        /** @var string $traceSummary */
        $traceSummary = $backtrace
            ? $this->flushBacktraceSummary($backtrace, $skip_frames)
            : wp_debug_backtrace_summary(null, $skip_frames);

        if ($this->config->debug || (WP_DEBUG && WP_DEBUG_LOG)) {
            error_log("objectcache.debug: Flushing object cache... {$traceSummary}");
        }

        if (
            $this->config->debug ||
            $this->config->save_commands ||
            WP_DEBUG ||
            $this->option('flushlog')
        ) {
            $log = (array) get_site_option('objectcache_flushlog', []);

            array_unshift($log, [
                'time' => time(),
                'user' => get_current_user_id() ?: null,
                'site' => $backtrace ? get_current_blog_id() : null,
                'cron' => wp_doing_cron(),
                'cli' => defined('WP_CLI') && WP_CLI,
                'trace' => $traceSummary,
            ]);

            update_site_option('objectcache_flushlog', array_slice($log, 0, 10));
        }
    }

    /**
     * Log cache group flush.
     *
     * @param  string  $group
     * @param  ?array<int, array<string, mixed>>  $backtrace
     * @return void
     */
    public function logGroupFlush($group, $backtrace = null)
    {
        /** @var string $traceSummary */
        $traceSummary = $backtrace
            ? $this->flushBacktraceSummary($backtrace, 1)
            : wp_debug_backtrace_summary(null, 1);

        if ($this->config->debug || (WP_DEBUG && WP_DEBUG_LOG)) {
            error_log("objectcache.debug: Flushing object cache group `{$group}`... {$traceSummary}");
        }

        if (
            $this->config->debug ||
            $this->config->save_commands ||
            WP_DEBUG ||
            $this->option('groupflushlog')
        ) {
            $log = (array) get_site_option('objectcache_flushlog_groups', []);

            array_unshift($log, [
                'group' => $group,
                'time' => time(),
                'user' => get_current_user_id() ?: null,
                'site' => $backtrace ? null : get_current_blog_id(),
                'cron' => wp_doing_cron(),
                'cli' => defined('WP_CLI') && WP_CLI,
                'trace' => $traceSummary,
            ]);

            update_site_option('objectcache_flushlog_groups', array_slice($log, 0, 10));
        }
    }

    /**
     * Simplified version of `wp_debug_backtrace_summary()`
     * that supports passing in the stacktrace.
     *
     * @param  array<int, array<string, mixed>>  $trace
     * @param  int  $skip_frames
     * @return string
     */
    protected function flushBacktraceSummary($trace, $skip_frames)
    {
        static $truncate_paths;

        $caller = [];

        if (! isset($truncate_paths)) {
            $truncate_paths = [
                wp_normalize_path(WP_CONTENT_DIR),
                wp_normalize_path(ABSPATH),
            ];
        }

        foreach ($trace as $call) {
            if ($skip_frames > 0) {
                $skip_frames--;
            } elseif (isset($call['class'])) {
                $caller[] = "{$call['class']}{$call['type']}{$call['function']}";
            } else {
                if (in_array($call['function'], ['do_action', 'apply_filters', 'do_action_ref_array', 'apply_filters_ref_array'], true)) {
                    $name = $call['args'][0] ?? '';
                    $caller[] = "{$call['function']}('{$name}')";
                } elseif (in_array($call['function'], ['include', 'include_once', 'require', 'require_once'], true)) {
                    $filename = $call['args'][0] ?? '';
                    $caller[] = $call['function'] . "('" . str_replace($truncate_paths, '', wp_normalize_path($filename)) . "')";
                } else {
                    $caller[] = $call['function'];
                }
            }
        }

        return implode(', ', array_reverse($caller));
    }
}
