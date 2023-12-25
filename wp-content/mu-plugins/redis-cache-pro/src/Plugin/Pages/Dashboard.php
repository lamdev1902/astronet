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

use RedisCachePro\Plugin\Api\Analytics;

use RedisCachePro\Metrics\RedisMetrics;
use RedisCachePro\Metrics\RelayMetrics;
use RedisCachePro\Metrics\WordPressMetrics;

class Dashboard extends Page
{
    /**
     * Whether to render analytics charts.
     *
     * @var bool
     */
    protected $renderAnalytics;

    /**
     * The requests/users interval.
     *
     * @var int
     */
    protected $interval;

    /**
     * The intervals.
     *
     * @var array<int, int>
     */
    protected $intervals;

    /**
     * Whether to refresh the analytics.
     *
     * @var bool
     */
    protected $refresh;

    /**
     * Returns the page title.
     *
     * @return string
     */
    public function title()
    {
        return 'Dashboard';
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

        $diagnostics = $this->plugin->diagnostics();

        $this->renderAnalytics = $this->plugin->analyticsEnabled()
            && $diagnostics->dropinExists()
            && $diagnostics->dropinIsValid()
            && $diagnostics->ping();

        add_action('admin_notices', [$this, 'healthNotices']);

        $this->setUpScreenOptions();
        $this->setUpWidgets();

        $this->enqueueScript();
        $this->enqueueAssets();
    }

    /**
     * Sets up the page's screen options.
     *
     * @return void
     */
    protected function setUpScreenOptions()
    {
        $userOptions = get_user_meta(get_current_user_id(), 'objectcache_screen_options', true);
        $this->refresh = $userOptions['refresh'] ?? true;

        $this->setIntervals($userOptions['interval'] ?? null);

        add_filter('screen_options_show_submit', '__return_true');
        add_filter('screen_options_show_screen', '__return_true', PHP_INT_MAX);
        add_filter('screen_settings', [$this, 'renderScreenSettings']);
        add_filter('default_hidden_meta_boxes', [$this, 'defaultHiddenMetrics']);

        add_screen_option('analytics_intervals', [
            'default' => $this->interval,
            'intervals' => $this->intervals,
        ]);

        add_screen_option('analytics_refresh', [
            'default' => true,
        ]);
    }

    /**
     * Sets the available intervals and the request's interval.
     *
     * @param  int  $userInterval
     * @return void
     */
    protected function setIntervals($userInterval)
    {
        $interval = Analytics::interval();
        $intervals = Analytics::intervals();

        if (array_key_exists($userInterval, $intervals)) {
            $interval = $userInterval;
        } elseif (! array_key_exists($interval, $intervals)) {
            $interval = array_keys($intervals)[0];
        }

        $this->interval = $interval;
        $this->intervals = $intervals;
    }

    /**
     * Registers the page's widgets.
     *
     * @return void
     */
    protected function setUpWidgets()
    {
        $this->addOverviewWidget();

        if ($this->renderAnalytics) {
            // $this->addRelayWidget();
            $this->addAnalyticsWidgets();
        }
    }

    /**
     * Enqueues the page's assets.
     *
     * @return void
     */
    protected function enqueueAssets()
    {
        \wp_enqueue_script('postbox');

        if ($this->renderAnalytics) {
            $this->plugin->enqueueAnalyticsAssets();
        }
    }

    /**
     * Returns extra data to be attached to `window.objectcache`.
     *
     * @return array<string, mixed>
     */
    protected function enqueueScriptExtra()
    {
        return [
            'refresh' => $this->refresh ? 10 : false,
            'interval' => $this->interval,
            'per_page' => Analytics::intervals()[$this->interval],
            'series' => [
                ['field' => 'median', 'name' => 'Median'],
            ],
            'comboCharts' => array_map(static function ($metric) {
                return [
                    'containers' => array_keys($metric['type']),
                    'labels' => $metric['labels'],
                ];
            }, $this->plugin->comboMetrics()),
        ];
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render()
    {
        require __DIR__ . '/../templates/pages/dashboard.phtml';
    }

    /**
     * Render the screen settings.
     *
     * @param  string  $screen_settings
     * @return string
     */
    public function renderScreenSettings($screen_settings)
    {
        ob_start();

        require __DIR__ . '/../templates/pages/screen-settings/dashboard.phtml';

        return $screen_settings . ob_get_clean();
    }

    /**
     * Hide some metrics by default.
     *
     * @param  array<string>  $hidden
     * @return array<string>
     */
    public function defaultHiddenMetrics($hidden)
    {
        return array_merge($hidden, [
            'objectcache_metric_hit_ratio',
            'objectcache_metric_hits',
            'objectcache_metric_misses',
            'objectcache_metric_bytes',
            'objectcache_metric_store_reads',
            'objectcache_metric_store_writes',
            'objectcache_metric_store_hits',
            'objectcache_metric_store_misses',
            'objectcache_metric_sql_queries',
            'objectcache_metric_ms_total',
            'objectcache_metric_ms_cache',
            'objectcache_metric_ms_cache_median',
            'objectcache_metric_ms_cache_ratio',
            'objectcache_metric_redis_hits',
            'objectcache_metric_redis_misses',
            'objectcache_metric_redis_hit_ratio',
            'objectcache_metric_redis_keys',
            'objectcache_metric_prefetches',
            'objectcache_metric_redis_used_memory',
            'objectcache_metric_redis_used_memory_rss',
            'objectcache_metric_redis_memory_ratio',
            'objectcache_metric_redis_memory_fragmentation_ratio',
            'objectcache_metric_redis_evicted_keys',
            'objectcache_metric_redis_connected_clients',
            'objectcache_metric_redis_tracking_clients',
            'objectcache_metric_redis_rejected_connections',
            'objectcache_metric_relay_hits',
            'objectcache_metric_relay_misses',
            'objectcache_metric_relay_hit_ratio',
            'objectcache_metric_relay_keys',
            'objectcache_metric_relay_memory_used',
            'objectcache_metric_relay_memory_total',
            'objectcache_metric_relay_memory_ratio',
        ]);
    }

    /**
     * Adds the "Overview" widget.
     *
     * @return void
     */
    protected function addOverviewWidget()
    {
        add_meta_box(
            'objectcache_overview',
            'Overview',
            [$this->plugin, 'renderWidget'],
            $this->plugin->screenId(),
            'normal'
        );
    }

    /**
     * Adds the "Relay" widget.
     *
     * @return void
     */
    protected function addRelayWidget()
    {
        if ($this->plugin->diagnostics()->usingRelay()) {
            return;
        }

        add_meta_box(
            'objectcache_relay',
            'Relay',
            function () {
                require __DIR__ . '/../templates/widgets/relay.phtml';
            },
            $this->plugin->screenId(),
            $this->plugin->analyticsEnabled() ? 'column4' : 'column3'
        );
    }

    /**
     * Add the all metrics as widgets.
     *
     * @return void
     */
    protected function addAnalyticsWidgets()
    {
        if (! $this->plugin->analyticsEnabled()) {
            add_meta_box(
                'objectcache_analytics',
                'Cache Analytics',
                function () {
                    require __DIR__ . '/../templates/widgets/analytics.phtml';
                },
                $this->plugin->screenId(),
                'side'
            );

            return;
        }

        $context = [
            'wp' => 'side',
            'redis' => 'column3',
            'relay' => 'column4',
        ];

        $usingRelay = $this->plugin->diagnostics()->usingRelayCache();

        $metrics = array_merge(
            WordPressMetrics::schema(),
            RedisMetrics::schema(),
            $usingRelay ? RelayMetrics::schema() : [],
            $this->plugin->comboMetrics()
        );

        $metrics = $this->filterMetrics($metrics);

        foreach ($metrics as $id => $metric) {
            $group = $metric['group'] === 'wp' ? '' : "{$metric['group']}:";

            $title = sprintf(
                '<span title="%3$s"><span>%1$s</span> %2$s</span>',
                ucfirst($group),
                esc_html($metric['title']),
                esc_html($metric['description'])
            );

            add_meta_box(
                sprintf('objectcache_metric_%s', str_replace('-', '_', $id)),
                $title,
                function () use ($id, $metric) { // @phpstan-ignore-line
                    require __DIR__ . '/../templates/widgets/metric.phtml';
                },
                $this->plugin->screenId(),
                $context[$metric['group']],
                'low'
            );
        }
    }

    /**
     * Removes forbidden metrics and sorts them by priority.
     *
     * @param  array<string>  $metrics
     * @return array<mixed>
     */
    protected function filterMetrics(array $metrics)
    {
        $order = [
            'requests',
            'commands',
            'response-times',

            'redis-requests',
            'redis-memory',
            'redis-ops-per-sec',
        ];

        if ($this->plugin->diagnostics()->usingRelayCache()) {
            array_push($order, ...[
                'relay-requests',
                'relay-memory',
                'relay-ops-per-sec',
            ]);
        }

        /**
         * Filters the default order and available metrics on the object cache dashboard.
         * Use `default_hidden_meta_boxes` filter to hide metrics by default without removing them.
         *
         * @param  array  $metrics  The available metrics.
         */
        $ids = (array) apply_filters(
            'objectcache_dashboard_metrics',
            array_merge($order, array_diff(array_keys($metrics), $order))
        );

        return array_combine($ids, array_map(function ($id) use ($metrics) {
            return $metrics[$id];
        }, $ids));
    }

    /**
     * Displays health notices.
     *
     * @return void
     */
    public function healthNotices()
    {
        $diagnostics = $this->plugin->diagnostics();

        $notice = function ($type, $text) {
            printf('<div class="update-nag notice notice-%s inline">%s</div>', $type, $text);
        };

        if ($diagnostics->dropinExists() && ! $diagnostics->dropinIsValid()) {
            $notice('error', implode(' ', [
                'WordPress is using a foreign object cache drop-in and Object Cache Pro is not being used.',
                'Use the Overview widget or WP CLI to enable the object cache drop-in.',
            ]));
        }
    }
}
