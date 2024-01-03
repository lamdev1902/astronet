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

use RedisCachePro\Diagnostics\Diagnostics;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Assets
{
    /**
     * Boot Assets component.
     *
     * @return void
     */
    public function bootAssets()
    {
        //
    }

    /**
     * Returns the URL to the given asset.
     *
     * @param  string  $path
     * @return string|false
     */
    public function asset($path)
    {
        if (Diagnostics::isMustUse()) {
            $plugin = $this->muAssetPath();

            return $plugin
                ? plugins_url("resources/{$path}", $plugin)
                : false;
        }

        return plugins_url("resources/{$path}", $this->filename);
    }

    /**
     * Returns the contents of the given asset.
     *
     * @param  string  $path
     * @return string
     */
    public function inlineAsset($path)
    {
        $asset = (string) file_get_contents(
            "{$this->directory}/resources/{$path}"
        );

        if (! defined('SCRIPT_DEBUG') || ! SCRIPT_DEBUG) {
            $asset = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\'|")\/\/.*))/', '', $asset);
            $asset = preg_replace('/(\v|\s{2,})/', ' ', $asset);
            $asset = preg_replace('/\s+/', ' ', $asset);
            $asset = trim($asset);
        }

        return $asset;
    }

    /**
     * Returns the must-use plugin path for usage with `plugins_url()`.
     *
     * @return string|null
     */
    protected function muAssetPath()
    {
        static $path;

        if (! $path) {
            $paths = [
                defined('WP_REDIS_DIR') ? rtrim(WP_REDIS_DIR, '/') : '',
                WPMU_PLUGIN_DIR . '/redis-cache-pro',
                WPMU_PLUGIN_DIR . '/object-cache-pro',
            ];

            foreach ($paths as $mupath) {
                if (strpos($mupath, WPMU_PLUGIN_DIR) === 0 && file_exists("{$mupath}/api.php")) {
                    $path = "{$mupath}/RedisWannaMine.php";
                }
            }
        }

        return $path;
    }
}
