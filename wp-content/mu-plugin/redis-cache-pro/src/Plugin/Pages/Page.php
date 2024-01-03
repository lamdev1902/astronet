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

use RedisCachePro\Plugin;
use const RedisCachePro\Version;

abstract class Page
{
    /**
     * The plugin instance.
     *
     * @var \RedisCachePro\Plugin
     */
    protected $plugin;

    /**
     * Creates a new instance.
     *
     * @param  \RedisCachePro\Plugin  $plugin
     * @return void
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Returns the page title.
     *
     * @return string
     */
    abstract public function title();

    /**
     * Returns the page slug.
     *
     * @return string
     */
    public function slug()
    {
        return strtolower(substr((string) strrchr(get_called_class(), '\\'), 1));
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    abstract public function render();

    /**
     * Boot the settings page and its components.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Enqueue the settings script.
     *
     * @return void
     */
    public function enqueueScript()
    {
        \wp_register_script('objectcache', false);
        \wp_enqueue_script('objectcache');

        \wp_localize_script(
            'objectcache',
            'objectcache',
            array_merge([
                'rest' => [
                    'nonce' => \wp_create_nonce('wp_rest'),
                    'url'  => \rest_url(),
                ],
                'gmt_offset' => \get_option('gmt_offset'),
            ], $this->enqueueScriptExtra())
        );
    }

    /**
     * Returns extra data to be attached to `window.objectcache`.
     *
     * @return array<mixed>
     */
    protected function enqueueScriptExtra()
    {
        return [];
    }

    /**
     * Enqueues the form script.
     *
     * @return void
     */
    protected function enqueueOptionsScript()
    {
        $script = $this->plugin->asset('js/options.js');

        \wp_register_script('objectcache-options', $script, ['jquery', 'wp-util', 'objectcache'], Version);
        \wp_enqueue_script('objectcache-options');

        if (! $script) {
            \wp_add_inline_script('objectcache-options', $this->plugin->inlineAsset('js/options.js'));
        }
    }

    /**
     * Returns the page's URL.
     *
     * @return string
     */
    public function url()
    {
        return network_admin_url(
            sprintf('%s&subpage=%s', $this->plugin->baseurl(), $this->slug())
        );
    }

    /**
     * Whether this page is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Whether this is the current page.
     *
     * @return bool
     */
    public function isCurrent()
    {
        return ($_GET['subpage'] ?? '') === $this->slug();
    }
}
