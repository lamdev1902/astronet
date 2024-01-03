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

use const RedisCachePro\Version;
use RedisCachePro\ObjectCaches\MeasuredObjectCacheInterface;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Analytics
{
    /**
     * Boot analytics component.
     *
     * @return void
     */
    public function bootAnalytics()
    {
        global $wp_object_cache;

        add_action('rest_api_init', [new Api\Analytics, 'register_routes']);

        if (! $this->analyticsEnabled()) {
            return;
        }

        if (! $wp_object_cache instanceof MeasuredObjectCacheInterface) {
            return;
        }

        add_action('wp_footer', [$this, 'shouldPrintMetricsComment']);
        add_action('wp_body_open', [$this, 'shouldPrintMetricsComment']);
        add_action('login_head', [$this, 'shouldPrintMetricsComment']);
        add_action('in_admin_header', [$this, 'shouldPrintMetricsComment']);
        add_action('rss_tag_pre', [$this, 'shouldPrintMetricsComment']);

        add_action('shutdown', [$this, 'maybePrintMetricsComment'], PHP_INT_MAX);

        add_action('objectcache_prune_analytics', [$this, 'pruneAnalytics']);

        if (wp_doing_cron() && ! wp_next_scheduled('objectcache_prune_analytics')) {
            wp_schedule_event(time(), 'hourly', 'objectcache_prune_analytics');
        }
    }

    /**
     * Whether analytics are enabled.
     *
     * @return bool
     */
    public function analyticsEnabled()
    {
        return $this->config->analytics->enabled;
    }

    /**
     * Callback for the scheduled `objectcache_prune_analytics` hook.
     *
     * @return void
     */
    public function pruneAnalytics()
    {
        global $wp_object_cache;

        $wp_object_cache->pruneMeasurements();
    }

    /**
     * Print the request's metrics as HTML comment.
     *
     * @return bool|void
     */
    public function shouldPrintMetricsComment()
    {
        static $shouldPrint;

        /**
         * Filters whether the analytics footnote is printed.
         *
         * @param  bool  $omit  Whether to omit printing the analytics footnote.
         */
        if ((bool) apply_filters('objectcache_omit_analytics_footnote', false)) {
            return;
        }

        if (doing_action('shutdown')) {
            return $shouldPrint;
        }

        $shouldPrint = true;
    }

    /**
     * Print the request's metrics as HTML comment.
     *
     * @return void
     */
    public function maybePrintMetricsComment()
    {
        global $wp_object_cache;

        if (
            ! \WP_DEBUG
            && ! $this->config->debug
            && ! $this->config->analytics->footnote
        ) {
            return;
        }

        if (! $this->shouldPrintMetricsComment()) {
            return;
        }

        if (is_robots() || is_trackback()) {
            return;
        }

        if (
            (defined('\WP_CLI') && constant('\WP_CLI')) ||
            (defined('\REST_REQUEST') && constant('\REST_REQUEST')) ||
            (defined('\XMLRPC_REQUEST') && constant('\XMLRPC_REQUEST')) ||
            (defined('\DOING_AJAX') && constant('\DOING_AJAX')) ||
            (defined('\DOING_CRON') && constant('\DOING_CRON')) ||
            (defined('\DOING_AUTOSAVE') && constant('\DOING_AUTOSAVE')) ||
            (function_exists('wp_is_json_request') && wp_is_json_request()) ||
            (function_exists('wp_is_jsonp_request') && wp_is_jsonp_request())
        ) {
            return;
        }

        if ($this->incompatibleContentType()) {
            return;
        }

        if (! $measurement = $wp_object_cache->requestMeasurement()) {
            return;
        }

        printf(
            "\n<!-- plugin=%s client=%s %s -->\n",
            'object-cache-pro',
            strtolower($wp_object_cache->clientName()),
            (string) $measurement
        );
    }

    /**
     * Enqueues the analytics assets.
     *
     * @return void
     */
    public function enqueueAnalyticsAssets()
    {
        $this->enqueueChartsAssets();

        $script = $this->asset('js/metrics.js');

        \wp_register_script('objectcache-analytics', $script, ['jquery', 'objectcache-charts'], Version);
        \wp_enqueue_script('objectcache-analytics');

        if (! $script) {
            \wp_add_inline_script('objectcache-analytics', $this->inlineAsset('js/metrics.js'));
        }
    }

    /**
     * Enqueues Apex Charts.
     *
     * @link https://apexcharts.com
     *
     * @return void
     */
    protected function enqueueChartsAssets()
    {
        $chartStyles = $this->asset('vendor/apexcharts/apexcharts.min.css');

        if ($chartStyles) {
            wp_enqueue_style('objectcache-charts', $chartStyles, [], Version);
        } else {
            wp_enqueue_style('objectcache-charts', 'https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.33.0/apexcharts.min.css', [], null);
            wp_script_add_data(
                'objectcache-charts',
                ['crossorigin', 'integrity', 'referrerpolicy'],
                ['anonymous', 'no-referrer', 'sha512-72LrFm5Wau6YFp7GGd7+qQJYkzRKj5UMQZ4aFuEo3WcRzO0xyAkVjK3NEw8wXjEsEG/skqvXKR5+VgOuzuqPtA==']
            );
        }

        $chartScript = $this->asset('vendor/apexcharts/apexcharts.min.js');

        if ($chartScript) {
            wp_enqueue_script('objectcache-charts', $chartScript, [], Version);
        } else {
            wp_enqueue_script('objectcache-charts', 'https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.33.0/apexcharts.min.js', [], null);
            wp_script_add_data(
                'objectcache-charts-js',
                ['crossorigin', 'integrity', 'referrerpolicy'],
                ['anonymous', 'no-referrer', 'sha512-s4UlxRFKE4p5qoQ+YnR53ttrA3s6qSmfjAXPMpznp60NLOUYJL1O4hgRfuFq/Dk0Uiw9xrsYzZSuEY8Y3gFsqw==']
            );
        }
    }

    /**
     * Whether the sent headers are incompatible with HTML comments.
     *
     * @see RedisCachePro\Plugin\Analytics::maybePrintMetricsComment()
     *
     * @return bool
     */
    protected function incompatibleContentType()
    {
        $jsonContentType = static function ($headers) {
            foreach ($headers as $header => $value) {
                if (stripos((string) $header, 'content-type') === false) {
                    continue;
                }

                if (stripos((string) $value, '/json') === false) {
                    continue;
                }

                return true;
            }

            return false;
        };

        if (function_exists('headers_list')) {
            $headers = [];

            foreach (headers_list() as $header) {
                [$name, $value] = explode(':', $header);
                $headers[$name] = $value;
            }

            if ($jsonContentType($headers)) {
                return true;
            }
        }

        if (function_exists('apache_response_headers')) {
            if ($headers = apache_response_headers()) {
                return $jsonContentType($headers);
            }
        }

        return false;
    }

    /**
     * Adds placeholder for the new combined charts.
     *
     * @return array<string, mixed>
     */
    public function comboMetrics()
    {
        $metrics = [
            'requests' => [
                'title' => 'Requests',
                'description' => 'The amount of times the cache data was and wasn’t already cached in memory and the in-memory hits-to-misses ratio.',
                'group' => 'wp',
                'type' => [
                    'hits' => 'integer',
                    'misses' => 'integer',
                    'hit-ratio' => 'ratio',
                ],
                'labels' => [
                    'hits' => 'Hits',
                    'misses' => 'Misses',
                    'hit-ratio' => 'Hit ratio',
                ],
            ],
            'commands' => [
                'title' => 'Commands',
                'description' => 'The number of times the cache read from and wrote to the datastore.',
                'group' => 'wp',
                'type' => [
                    'store-reads' => 'integer',
                    'store-writes' => 'integer',
                ],
                'labels' => [
                    'store-reads' => 'Datastore reads',
                    'store-writes' => 'Datastore writes',
                ],
            ],
            'response-times' => [
                'title' => 'Response Times',
                'description' => 'The amount of time (ms) WordPress took to render the request and waited for the datastore to respond.',
                'group' => 'wp',
                'type' => [
                    'ms-total' => 'time',
                    'ms-cache' => 'time',
                    'ms-cache-ratio' => 'ratio',
                ],
                'labels' => [
                    'ms-total' => 'Request',
                    'ms-cache' => 'Cache',
                    'ms-cache-ratio' => 'Cache ratio',
                ],
            ],
            'redis-requests' => [
                'title' => 'Requests',
                'description' => 'Number of successful and failed key lookups and the hits-to-misses ratio.',
                'group' => 'redis',
                'type' => [
                    'redis-hits' => 'integer',
                    'redis-misses' => 'integer',
                    'redis-hit-ratio' => 'ratio',
                ],
                'labels' => [
                    'redis-hits' => 'Hits',
                    'redis-misses' => 'Misses',
                    'redis-hit-ratio' => 'Hit ratio',
                ],
            ],
            'redis-memory' => [
                'title' => 'Memory',
                'description' => 'The ratio of memory allocated by Redis compared to the maximum amount of memory allocatable by Redis.',
                'group' => 'redis',
                'type' => [
                    'redis-used-memory' => 'bytes',
                    'redis-memory-ratio' => 'ratio',
                    'redis-memory-fragmentation-ratio' => 'ratio',
                ],
                'labels' => [
                    'redis-used-memory' => 'Used memory',
                    'redis-memory-ratio' => 'Memory ratio',
                    'redis-memory-fragmentation-ratio' => 'Fragmentation ratio',
                ],
            ],
        ];

        if (! $this->diagnostics()->maxMemory()) {
            unset($metrics['redis-memory']['type']['redis-memory-ratio']);
        }

        if ($this->diagnostics()->usingRelayCache()) {
            $metrics['relay-requests'] = [
                'title' => 'Requests',
                'description' => 'Number of successful and failed key lookups and the hits-to-misses ratio.',
                'group' => 'relay',
                'type' => [
                    'relay-hits' => 'integer',
                    'relay-misses' => 'integer',
                    'relay-hit-ratio' => 'ratio',
                ],
                'labels' => [
                    'relay-hits' => 'Hits',
                    'relay-misses' => 'Misses',
                    'relay-hit-ratio' => 'Hit ratio',
                ],
            ];

            $metrics['relay-memory'] = [
                'title' => 'Memory',
                'description' => 'The ratio between the amount of bytes pointing to live objects including metadata and the total amount of memory mapped into the allocator.',
                'group' => 'relay',
                'type' => [
                    'relay-memory-total' => 'integer',
                    'relay-memory-used' => 'integer',
                    'relay-memory-ratio' => 'ratio',
                ],
                'labels' => [
                    'relay-memory-total' => 'Total memory',
                    'relay-memory-used' => 'Used memory',
                    'relay-memory-ratio' => 'Memory ratio',
                ],
            ];
        }

        return $metrics;
    }
}
