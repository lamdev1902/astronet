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

namespace RedisCachePro\Plugin\Extensions;

use RedisCachePro_DebugBar_Insights;
use RedisCachePro\ObjectCaches\ObjectCache;

trait Debugbar
{
    /**
     * Boot Debug Bar component and register panels and statuses.
     *
     * @return void
     */
    public function bootDebugbar()
    {
        if (! is_plugin_active('debug-bar/debug-bar.php')) {
            return;
        }

        require_once "{$this->directory}/src/Extensions/Debugbar/Panel.php";
        require_once "{$this->directory}/src/Extensions/Debugbar/Insights.php";

        add_action('debug_bar_panels', [$this, 'panels']);
        add_action('debug_bar_statuses', [$this, 'statuses']);
    }

    /**
     * Register the default diagnostics debug bar panel, as well as
     * other panels provided by the object cache.
     *
     * @param  array<mixed>  $panels
     * @return array<mixed>
     */
    public function panels($panels)
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCache) {
            return $panels;
        }

        $panels[] = new RedisCachePro_DebugBar_Insights($wp_object_cache);

        return $panels;
    }

    /**
     * Add the Redis version to the debug bar statuses.
     *
     * @param  array<string, mixed>  $statuses
     * @return array<string, mixed>
     */
    public function statuses($statuses)
    {
        $diagnostics = $this->diagnostics()->toArray();

        $version = $diagnostics['versions']['redis'];
        $memory = $diagnostics['statistics']['redis-memory'];

        if ($version->value) {
            $position = array_search('db', array_column($statuses, 0));

            array_splice($statuses, $position + 1, 0, [['redis', 'Redis', $version]]);
        }

        if ($memory->value) {
            $position = array_search('redis-memory', array_column($statuses, 0));

            array_splice($statuses, $position + 1, 0, [['redis-memory', 'Redis Memory', $memory]]);
        }

        return $statuses;
    }
}
