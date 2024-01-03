<?php
/*
 * Plugin Name: Object Cache Pro
 * Plugin URI: https://objectcache.pro
 * Description: A business class Redis object cache backend for WordPress.
 * Version: 1.20.2
 * Author: Rhubarb Group
 * Author URI: https://rhubarb.group
 * License: Proprietary
 * Update URI: false
 * Network: true
 * Requires PHP: 7.2
 */

defined('ABSPATH') || exit;

/**
 * Abort plugin boot on unsupported PHP versions.
 */
if (version_compare(PHP_VERSION, '7.2', '<')) {
    return;
}

/**
 * Avoid loading plugin more than once.
 */
if (defined('RedisCachePro\Version')) {
    return error_log('objectcache.notice: Object Cache Pro is being loaded more than once');
}

/**
 * The plugin version number.
 */
define('RedisCachePro\Version', '1.20.2');

/**
 * The absolute path to the plugin file.
 */
define('RedisCachePro\Filename', __FILE__);

/**
 * Bootstrap the plugin and instantiate it.
 */
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

require_once __DIR__ . '/bootstrap.php';

add_action('plugins_loaded', function () {
    if (! defined('RedisCachePro\Basename')) {
        define('RedisCachePro\Basename', plugin_basename(__FILE__));
    }

    $GLOBALS['ObjectCachePro'] = \RedisCachePro\Plugin::boot();

    // `$GLOBALS['RedisCachePro']` is deprecated since version 1.14.0!
    // Use `$GLOBALS['ObjectCachePro']` instead.
    $GLOBALS['RedisCachePro'] = $GLOBALS['ObjectCachePro'];

    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::get_root_command()->remove_subcommand('redis');
        WP_CLI::add_command('redis', \RedisCachePro\Console\Commands::class);
    }
});

add_action('activated_plugin', function ($plugin) {
    global $wp_filesystem;

    if ($plugin !== plugin_basename(__FILE__)) {
        return;
    }

    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::log(WP_CLI::colorize('Be sure to set up the `%gWP_REDIS_CONFIG%n` constant before running `%gwp redis enable%n`.'));
    } else {
        set_transient('objectcache_activated', wp_create_nonce('objectcache-activated'), 30);
    }

    deactivate_plugins('redis-cache/redis-cache.php', true, is_multisite());

    if (WP_Filesystem()) {
        $dropin = WP_CONTENT_DIR . '/object-cache.php';

        if ($wp_filesystem->exists($dropin)) {
            $data = get_plugin_data($dropin, false, false);

            if (strpos($data['PluginURI'], 'wordpress.org/plugins/redis-cache')) {
                $wp_filesystem->delete($dropin);

                if (function_exists('wp_opcache_invalidate')) {
                    wp_opcache_invalidate($dropin, true);
                }
            }
        }
    }
});
