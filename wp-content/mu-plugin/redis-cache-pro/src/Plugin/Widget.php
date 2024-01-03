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

use WP_Screen;

use RedisCachePro\Plugin;

use RedisCachePro\Metrics\RedisMetrics;
use RedisCachePro\Metrics\RelayMetrics;
use RedisCachePro\Metrics\WordPressMetrics;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Widget
{
    /**
     * Whitelist of widget actions.
     *
     * @var array<string>
     */
    protected $widgetActions = [
        'flush-cache',
        'flush-site-cache',
        'flush-network-cache',
        'enable-dropin',
        'update-dropin',
        'disable-dropin',
    ];

    /**
     * Whitelist of widget action statuses.
     *
     * @var array<string>
     */
    protected $widgetActionStatuses = [
        'cache-flushed',
        'cache-not-flushed',
        'site-cache-flushed',
        'site-cache-not-flushed',
        'network-cache-flushed',
        'network-cache-not-flushed',
        'dropin-enabled',
        'dropin-not-enabled',
        'dropin-updated',
        'dropin-not-updated',
        'dropin-disabled',
        'dropin-not-disabled',
    ];

    /**
     * Boot widget component.
     *
     * @return void
     */
    public function bootWidget()
    {
        add_action('current_screen', [$this, 'registerWidget']);
    }

    /**
     * Register the dashboard widgets.
     *
     * @param  \WP_Screen  $screen
     * @return void
     */
    public function registerWidget(WP_Screen $screen)
    {
        if (! in_array($screen->id, ['dashboard', 'dashboard-network', $this->screenId])) {
            return;
        }

        if (! current_user_can(Plugin::Capability)) {
            return;
        }

        $pageHook = str_replace('-network', '', $this->screenId);

        add_action('load-index.php', [$this, 'handleWidgetActions']);
        add_action("load-{$pageHook}", [$this, 'handleWidgetActions']);

        add_action('admin_notices', [$this, 'displayWidgetNotice'], 0);
        add_action('network_admin_notices', [$this, 'displayWidgetNotice'], 0);

        add_action('admin_enqueue_scripts', [$this, 'addWidgetStyles']);

        if ($screen->id !== $this->screenId) {
            $this->enqueueWidgetScripts();
        }

        /**
         * Filters whether to add the dashboard widget.
         *
         * @param  bool  $add_widget  Whether to add the dashboard widget. Default `true`.
         */
        if ((bool) apply_filters('objectcache_dashboard_widget', true)) {
            add_action('wp_dashboard_setup', function () {
                wp_add_dashboard_widget('dashboard_objectcache', 'Object Cache Pro', [$this, 'renderWidget'], null, null, 'normal', 'high');
            });
        }

        /**
         * Filters whether to add the network dashboard widget.
         *
         * @param  bool  $add_widget  Whether to add the network dashboard widget. Default `true`.
         */
        if ((bool) apply_filters('objectcache_network_dashboard_widget', true)) {
            add_action('wp_network_dashboard_setup', function () {
                wp_add_dashboard_widget('dashboard_objectcache', 'Object Cache Pro', [$this, 'renderWidget'], null, null, 'normal', 'high');
            });
        }
    }

    /**
     * Render the dashboard widget.
     *
     * @return void
     */
    public function renderWidget()
    {
        global $wp_object_cache_errors;

        require __DIR__ . '/templates/widgets/overview.phtml';
    }

    /**
     * Handle widget actions and redirect back to dashboard.
     *
     * @return void
     */
    public function handleWidgetActions()
    {
        global $wp_object_cache;

        $screenId = get_current_screen()->id ?? null;
        $actionParameter = $screenId === $this->screenId ? 'action' : 'objectcache-action';

        $nonce = $_GET['_wpnonce'] ?? false;
        $action = $_GET[$actionParameter] ?? false;

        if (! $action || ! $nonce) {
            return;
        }

        if (! in_array($action, $this->widgetActions)) {
            wp_die('Invalid action.', 400);
        }

        if (! \wp_verify_nonce($nonce, $action)) {
            wp_die("Invalid nonce for {$action} action.", 400);
        }

        if (is_multisite() && ! is_network_admin() && ! in_array($action, ['flush-cache', 'flush-site-cache'])) {
            wp_die("Sorry, you are not allowed to perform the {$action} action.", 403);
        }

        $status = null;

        switch ($action) {
            case 'flush-cache':
                $this->logFlush();
                $status = $wp_object_cache->flush() ? 'cache-flushed' : 'cache-not-flushed';
                break;
            case 'flush-site-cache':
                $status = $wp_object_cache->flushBlog() ? 'site-cache-flushed' : 'site-cache-not-flushed';
                break;
            case 'flush-network-cache':
                $this->logFlush();
                $status = $wp_object_cache->flush() ? 'network-cache-flushed' : 'network-cache-not-flushed';
                break;
            case 'enable-dropin':
                $status = $this->enableDropin() ? 'dropin-enabled' : 'dropin-not-enabled';
                break;
            case 'update-dropin':
                $status = $this->updateDropin() ? 'dropin-updated' : 'dropin-not-updated';
                break;
            case 'disable-dropin':
                $status = $this->disableDropin() ? 'dropin-disabled' : 'dropin-not-disabled';
                break;
        }

        if ($screenId === $this->screenId) {
            $url = add_query_arg('status', $status, $this->baseurl);
        } else {
            $url = add_query_arg('objectcache-status', $status, is_network_admin() ? network_admin_url() : admin_url());
        }

        wp_safe_redirect($url, 302, 'Object Cache Pro');
        exit;
    }

    /**
     * Print the widget styles inlines to support non-standard installs.
     *
     * @return void
     */
    public function addWidgetStyles()
    {
        wp_add_inline_style('dashboard', $this->inlineAsset('css/widget.css'));
    }

    /**
     * Enqueue the widget scripts.
     *
     * @return void
     */
    protected function enqueueWidgetScripts()
    {
        if (! $this->analyticsEnabled()) {
            return;
        }

        \wp_register_script('objectcache', false);
        \wp_enqueue_script('objectcache');

        \wp_localize_script('objectcache', 'objectcache', [
            'rest' => [
                'nonce' => \wp_create_nonce('wp_rest'),
                'url'  => \rest_url(),
            ],
            'gmt_offset' => \get_option('gmt_offset'),
            'refresh' => 30,
            'interval' => 60,
            'series' => [
                ['field' => 'median', 'name' => 'Median'],
            ],
            'comboCharts' => array_map(static function ($metric) {
                return [
                    'containers' => array_keys($metric['type']),
                    'labels' => $metric['labels'],
                ];
            }, $this->comboMetrics()),
        ]);

        $this->enqueueAnalyticsAssets();
    }

    /**
     * Display status notices for widget actions.
     *
     * @return void
     */
    public function displayWidgetNotice()
    {
        $status = $_GET['status'] ?? $_GET['objectcache-status'] ?? false;

        if (! $status || ! in_array($status, $this->widgetActionStatuses)) {
            return;
        }

        $notice = function ($type, $text) {
            return sprintf('<div class="notice notice-%s"><p>%s</p></div>', $type, $text);
        };

        switch ($status) {
            case 'cache-flushed':
                echo $notice('success', 'The object cache was flushed.');
                break;
            case 'cache-not-flushed':
                echo $notice('error', 'The object cache could not be flushed.');
                break;
            case 'site-cache-flushed':
                echo $notice('success', 'This site’s object cache was flushed.');
                break;
            case 'site-cache-not-flushed':
                echo $notice('error', 'This site’s object cache could not be flushed.');
                break;
            case 'network-cache-flushed':
                echo $notice('success', 'The network’s object cache was flushed.');
                break;
            case 'network-cache-not-flushed':
                echo $notice('error', 'The network’s object cache could not be flushed.');
                break;
            case 'dropin-enabled':
                echo $notice('success', 'The object cache drop-in was enabled.');
                break;
            case 'dropin-not-enabled':
                echo $notice('error', 'The object cache drop-in could not be enabled.');
                break;
            case 'dropin-updated':
                echo $notice('success', 'The object cache drop-in was updated.');
                break;
            case 'dropin-not-updated':
                echo $notice('error', 'The object cache drop-in could not be updated.');
                break;
            case 'dropin-disabled':
                echo $notice('success', 'The object cache drop-in was disabled.');
                break;
            case 'dropin-not-disabled':
                echo $notice('error', 'The object cache drop-in could not be disabled.');
                break;
        }
    }

    /**
     * Add the all metrics as widgets.
     *
     * @return array<string, mixed>
     */
    protected function widgetCharts()
    {
        $charts = [
            'response-times',
            'requests',
        ];

        $metrics = array_merge(
            WordPressMetrics::schema(),
            RedisMetrics::schema(),
            $this->comboMetrics()
        );

        if ($this->diagnostics()->usingRelayCache()) {
            $charts[] = 'relay-requests';
            $metrics = array_merge($metrics, RelayMetrics::schema());
        }

        /**
         * Filters the default order and available metrics on the dashboard widget.
         *
         * @param  array  $metrics  The available metrics.
         */
        $charts = (array) apply_filters('objectcache_widget_metrics', $charts);

        return array_combine($charts, array_map(function ($id) use ($metrics) {
            return $metrics[$id];
        }, $charts));
    }
}
