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

use Throwable;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Dropin
{
    /**
     * Boot dropin component.
     *
     * @return void
     */
    public function bootDropin()
    {
        add_action('file_mod_allowed', [$this, 'applyFileModFilters'], 10, 2);
        add_action('upgrader_process_complete', [$this, 'maybeUpdateDropin'], 10, 2);

        // prevent server-timing drop-in hijack
        add_filter('perflab_disable_object_cache_dropin', '__return_true');
    }

    /**
     * Adds shortcut filters to core's `file_mod_allowed` filter.
     *
     * @param  bool  $file_mod_allowed
     * @param  string  $context
     * @return bool
     */
    public function applyFileModFilters($file_mod_allowed, $context)
    {
        if ($context === 'object_cache_dropin') {
            /**
             * Filters whether drop-in file modifications are allowed.
             *
             * @param  bool  $dropin_mod_allowed  Whether drop-in modifications are allowed.
             */
            return (bool) apply_filters('objectcache_allow_dropin_mod', true);
        }

        return $file_mod_allowed;
    }

    /**
     * Attempt to enable the object cache drop-in.
     *
     * @return bool
     */
    public function enableDropin()
    {
        global $wp_filesystem;

        if (! \WP_Filesystem()) {
            return false;
        }

        $dropin = \WP_CONTENT_DIR . '/object-cache.php';
        $stub = "{$this->directory}/stubs/object-cache.php";

        $result = $wp_filesystem->copy($stub, $dropin, true, FS_CHMOD_FILE);

        if (function_exists('wp_opcache_invalidate')) {
            wp_opcache_invalidate($dropin, true);
        }

        /**
         * Filters whether to automatically flush the object after enabling the drop-in.
         *
         * @param  bool  $autoflush  Whether to auto-flush the object cache. Default true.
         */
        if ((bool) apply_filters('objectcache_autoflush', true)) {
            $this->resetCache();
        }

        /**
         * Filters whether to delete transients from the database after enabling the drop-in.
         *
         * @param  bool  $delete  Whether to delete the transients. Default true.
         */
        if ((bool) apply_filters('objectcache_cleanup_transients', true)) {
            add_action('shutdown', [$this, 'deleteTransients']);
        }

        return $result;
    }

    /**
     * Attempt to disable the object cache drop-in.
     *
     * @return bool
     */
    public function disableDropin()
    {
        global $wp_filesystem;

        if (! \WP_Filesystem()) {
            return false;
        }

        $dropin = \WP_CONTENT_DIR . '/object-cache.php';

        if (! $wp_filesystem->exists($dropin)) {
            return false;
        }

        $result = $wp_filesystem->delete($dropin);

        if (function_exists('wp_opcache_invalidate')) {
            wp_opcache_invalidate($dropin, true);
        }

        /**
         * Filters whether to automatically flush the object after disabling the drop-in.
         *
         * @param  bool  $autoflush  Whether to auto-flush the object cache. Default true.
         */
        if ((bool) apply_filters('objectcache_autoflush', true)) {
            $this->resetCache();
        }

        return $result;
    }

    /**
     * Attempt to update the object cache drop-in.
     *
     * @return bool
     */
    public function updateDropin()
    {
        global $wp_filesystem;

        if (! \WP_Filesystem()) {
            return false;
        }

        $dropin = \WP_CONTENT_DIR . '/object-cache.php';
        $stub = "{$this->directory}/stubs/object-cache.php";

        $result = $wp_filesystem->copy($stub, $dropin, true, FS_CHMOD_FILE);

        if (function_exists('wp_opcache_invalidate')) {
            wp_opcache_invalidate($dropin, true);
        }

        return $result;
    }

    /**
     * Update the object cache drop-in, if it's outdated.
     *
     * @param  \WP_Upgrader  $upgrader
     * @param  array<string, mixed>  $options
     * @return bool|void
     */
    public function maybeUpdateDropin($upgrader, $options)
    {
        $this->verifyDropin();

        if (! wp_is_file_mod_allowed('object_cache_dropin')) {
            return;
        }

        if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
            return;
        }

        if (! in_array($this->basename, $options['plugins'] ?? [])) {
            return;
        }

        $diagnostics = $this->diagnostics();

        if (! $diagnostics->dropinExists() || ! $diagnostics->dropinIsValid()) {
            return;
        }

        if ($diagnostics->dropinIsUpToDate()) {
            return;
        }

        return $this->updateDropin();
    }

    /**
     * Verifies the object cache drop-in.
     *
     * @return void
     */
    public function verifyDropin()
    {
        if (! $this->license()->isValid()) {
            $this->disableDropin();
        }
    }

    /**
     * Initializes and connects the WordPress Filesystem Abstraction classes.
     *
     * @return \WP_Filesystem_Base
     */
    protected function wpFilesystem()
    {
        global $wp_filesystem;

        try {
            require_once \ABSPATH . 'wp-admin/includes/plugin.php';
        } catch (Throwable $th) {
            //
        }

        if (! \WP_Filesystem()) {
            try {
                require_once \ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
                require_once \ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
            } catch (Throwable $th) {
                //
            }

            return new \WP_Filesystem_Direct(null);
        }

        return $wp_filesystem;
    }
}
