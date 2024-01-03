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

namespace RedisCachePro;

use RedisCachePro\Diagnostics\Diagnostics;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\ObjectCaches\ObjectCacheInterface;

final class Plugin
{
    use Plugin\Extensions\Debugbar,
        Plugin\Extensions\QueryMonitor,
        Plugin\Analytics,
        Plugin\Assets,
        Plugin\Authorization,
        Plugin\Dropin,
        Plugin\Health,
        Plugin\Licensing,
        Plugin\Lifecycle,
        Plugin\Meta,
        Plugin\Network,
        Plugin\Settings,
        Plugin\Transients,
        Plugin\Updates,
        Plugin\Widget;

    /**
     * The configuration instance.
     *
     * @var \RedisCachePro\Configuration\Configuration
     */
    protected $config;

    /**
     * Holds the plugin version number.
     *
     * @var string
     */
    protected $version;

    /**
     * Holds the plugin basename.
     *
     * @var string
     */
    protected $basename;

    /**
     * Holds the plugin directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Holds the plugin filename.
     *
     * @var string
     */
    protected $filename;

    /**
     * Holds the plugin website.
     *
     * @var string
     */
    const Url = 'https://objectcache.pro';

    /**
     * The capability required to manage this plugin.
     *
     * @var string
     */
    const Capability = 'objectcache_manage';

    /**
     * Initialize the plugin, load all extensions and register lifecycle hooks.
     *
     * @return self
     */
    public static function boot()
    {
        global $wp_object_cache;

        $instance = new static;
        $instance->version = Version;
        $instance->basename = Basename;
        $instance->filename = Filename;
        $instance->directory = (string) realpath(__DIR__ . '/..');

        if ($wp_object_cache instanceof ObjectCacheInterface) {
            $instance->config = $wp_object_cache->config();
        } else {
            $instance->config = Configuration::safelyFrom(
                defined('\WP_REDIS_CONFIG') ? \WP_REDIS_CONFIG : []
            );
        }

        foreach ((array) class_uses($instance) as $class) {
            $name = substr((string) $class, strrpos((string) $class, '\\') + 1);

            $instance->{"boot{$name}"}();
        }

        return $instance;
    }

    /**
     * Returns the raw basename.
     *
     * @return string
     */
    public function basename()
    {
        return $this->basename;
    }

    /**
     * Returns the cleaned up basename.
     *
     * @return string
     */
    public function slug()
    {
        return strpos($this->basename, '/') === false
            ? $this->basename
            : dirname($this->basename);
    }

    /**
     * Returns the configuration instance.
     *
     * @return \RedisCachePro\Configuration\Configuration
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Returns a singleton diagnostics instance.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostics
     */
    public function diagnostics()
    {
        global $wp_object_cache;

        static $diagnostics = null;

        if (! $diagnostics) {
            $diagnostics = new Diagnostics($wp_object_cache);
        }

        return $diagnostics;
    }
}
