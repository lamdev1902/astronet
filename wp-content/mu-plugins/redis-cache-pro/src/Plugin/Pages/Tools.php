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

namespace RedisCachePro\Plugin\Pages;

use RedisCachePro\ObjectCaches\ObjectCache;

use const RedisCachePro\Version;

class Tools extends Page
{
    /**
     * Returns the page title.
     *
     * @return string
     */
    public function title()
    {
        return 'Tools';
    }

    /**
     * Returns the page slug.
     *
     * @return string
     */
    public function slug()
    {
        return 'tools';
    }

    /**
     * Whether this page is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        global $wp_object_cache;

        return $wp_object_cache instanceof ObjectCache;
    }

    /**
     * Boot the settings page and its components.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->isCurrent()) {
            return;
        }

        add_filter('screen_options_show_screen', '__return_true', PHP_INT_MAX);

        $this->addLatencyWidget();
        $this->addGroupsWidget();
        $this->addFlushLogWidget();
        $this->addGroupFlushLogWidget();

        $this->enqueueScript();
        $this->enqueueAssets();
    }

    /**
     * Enqueues the assets.
     *
     * @return void
     */
    protected function enqueueAssets()
    {
        $script = $this->plugin->asset('js/tools.js');

        wp_enqueue_script('postbox');

        wp_register_script('objectcache-tools', $script, ['jquery', 'clipboard'], Version);
        wp_enqueue_script('objectcache-tools');

        if (! $script) {
            wp_add_inline_script('objectcache-tools', $this->plugin->inlineAsset('js/tools.js'));
        }
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render()
    {
        require __DIR__ . '/../templates/pages/tools.phtml';
    }

    /**
     * Adds the "Connection Latency" widget.
     *
     * @return void
     */
    protected function addLatencyWidget()
    {
        add_meta_box(
            'objectcache_latency',
            'Latency',
            function () {
                require __DIR__ . '/../templates/widgets/tools/latency.phtml';
            },
            $this->plugin->screenId(),
            'normal'
        );
    }

    /**
     * Adds the "Cache Groups" widget.
     *
     * @return void
     */
    protected function addGroupsWidget()
    {
        add_meta_box(
            'objectcache_groups',
            'Groups',
            function () {
                require __DIR__ . '/../templates/widgets/tools/groups.phtml';
            },
            $this->plugin->screenId(),
            'side'
        );
    }

    /**
     * Adds the "Flush Log" widget.
     *
     * @return void
     */
    protected function addFlushLogWidget()
    {
        add_meta_box(
            'objectcache_flushlog',
            'Flush log',
            function () {
                require __DIR__ . '/../templates/widgets/tools/flushlog.phtml';
            },
            $this->plugin->screenId(),
            'normal'
        );
    }

    /**
     * Adds the "Flush Group Log" widget.
     *
     * @return void
     */
    protected function addGroupFlushLogWidget()
    {
        add_meta_box(
            'objectcache_groupflushlog',
            'Group flush log',
            function () {
                require __DIR__ . '/../templates/widgets/tools/flushlog-groups.phtml';
            },
            $this->plugin->screenId(),
            'normal'
        );
    }

    /**
     * Returns the caller name for given flush-log backtrace.
     *
     * @param  string  $backtrace
     * @return string
     */
    protected function flushlogCaller(string $backtrace)
    {
        /**
         * Filters the cache flush caller.
         *
         * @param  string  $caller  The cache flush caller.
         * @param  string  $backtrace  The comma-separated string of functions that have been called.
         */
        $caller = (string) apply_filters(
            'objectcache_flushlog_caller',
            (string) strstr($backtrace, ',', true),
            $backtrace
        );

        if (strpos($caller, 'Plugin->handleWidgetActions')) {
            return 'Dashboard widget';
        }

        if (strpos($caller, 'Plugin->enableDropin')) {
            return 'Drop-in enabled';
        }

        if (strpos($caller, 'Plugin->disableDropin')) {
            return 'Drop-in disabled';
        }

        if (strpos($caller, '->bootMetadata')) {
            return 'Integrity protection';
        }

        if ($caller == 'Cache_Command->flush') {
            return 'wp cache flush';
        }

        if (strpos($caller, 'Commands->enable')) {
            return 'wp redis enable';
        }

        if (strpos($caller, 'Commands->disable')) {
            return 'wp redis disable';
        }

        if (strpos($caller, 'Commands->flushGroup')) {
            return 'wp redis flush-group';
        }

        if (strpos($caller, 'Commands->flush')) {
            return 'wp redis flush';
        }

        if (strpos($caller, 'Commands->reset')) {
            return 'wp redis reset';
        }

        if (strpos($caller, 'Plugin\Api\\')) {
            return 'REST API';
        }

        return $caller;
    }

    /**
     * Returns a clean, formatted backtrace for a flushlog entry.
     *
     * @param  string  $backtrace
     * @return string
     */
    protected function flushlogBacktrace(string $backtrace)
    {
        $frames = array_reverse(explode(', ', $backtrace));

        $stack = array_filter($frames, static function ($frame) {
            return ! preg_match('/^(include|require)(_once)?\(/', $frame);
        });

        if (empty($stack)) {
            $stack = $frames;
        }

        $stack = array_filter($stack, static function ($frame) {
            return ! in_array($frame, [
                'call_user_func',
                'call_user_func_array',
                'WP_Hook->do_action',
                'WP_Hook->do_action_ref_array',
                'WP_Hook->apply_filters',
                'WP_Hook->apply_filters_ref_array',
                'RedisCachePro\Plugin->flush',
                'RedisCachePro\Plugin->maybeLogFlush',
                'RedisCachePro\Plugin->maybeLogGroupFlush',
                "apply_filters('pre_objectcache_flush')",
                "apply_filters('pre_objectcache_flush_group')",
                'wp_cache_flush',
                'wp_cache_flush_group',
            ]);
        });

        return implode(', ', array_slice($stack, 0, 5));
    }
}
