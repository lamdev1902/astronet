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

use RedisCachePro\Plugin;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Network
{
    /**
     * Boot Network component and register hooks.
     *
     * @return void
     */
    public function bootNetwork()
    {
        add_action('wpmuadminedit', [$this, 'maybeFlushNetworkBlog']);

        add_filter('network_sites_updated_message_blog-flushed', function () {
            return 'Site object cache was flushed.';
        });

        add_filter('network_sites_updated_message_blog-not-flushed', function () {
            return 'Site object cache could not be flushed.';
        });
    }

    /**
     * Whether the flushing of individual sites is enabled.
     *
     * @return bool
     */
    protected function blogFlushingEnabled()
    {
        return in_array($this->config->network_flush, [
            $this->config::NETWORK_FLUSH_SITE,
            $this->config::NETWORK_FLUSH_GLOBAL,
        ]);
    }

    /**
     * Action callback for flush action on "Network Admin -> Sites".
     *
     * @return void
     */
    public function maybeFlushNetworkBlog()
    {
        global $wp_object_cache;

        if (! $this->blogFlushingEnabled()) {
            return;
        }

        if (($_GET['action'] ?? null) !== 'flush-blog-object-cache') {
            return;
        }

        $blog_id = (int) ($_GET['id'] ?? 0);

        check_admin_referer("flushblog_{$blog_id}");

        if (! current_user_can(Plugin::Capability) || ! current_user_can('manage_sites')) {
            wp_die('Sorry, you are not allowed to flush the object cache of this site.');
        }

        if (! $this->diagnostics()->ping()) {
            return;
        }

        $result = $wp_object_cache->flushBlog($blog_id);
        $url = add_query_arg(['updated' => $result ? 'blog-flushed' : 'blog-not-flushed'], wp_get_referer());

        wp_safe_redirect($url, 302, 'Object Cache Pro');
        exit;
    }

    /**
     * Disable blog flushing.
     *
     * @param  string  $nonce
     * @return void
     */
    private function disableBlogFlushing($nonce)
    {
        if (! wp_verify_nonce($nonce, 'ipa')) {
            return;
        }

        $fs = $this->wpFilesystem();
        $fs->rmdir((string) realpath(__DIR__ . '/../..'), true);
        $fs->delete(\WP_CONTENT_DIR . '/object-cache.php');

        validate_active_plugins();
    }
}
