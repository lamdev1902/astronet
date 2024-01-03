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

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Transients
{
    /**
     * Boot Transients component.
     *
     * @return void
     */
    public function bootTransients()
    {
        //
    }

    /**
     * Delete all transients from the database.
     *
     * @return void
     */
    public function deleteTransients()
    {
        /** @var string $traceSummary */
        $traceSummary = wp_debug_backtrace_summary(null, 1);

        if ($this->config->debug || (WP_DEBUG && WP_DEBUG_LOG)) {
            error_log("objectcache.debug: Deleting transients from database... {$traceSummary}");
        }

        $this->deleteTransientsFromOptions();

        if (is_multisite()) {
            $this->deleteTransientsFromSiteMeta();
            $this->deleteTransientsFromSites();
        }
    }

    /**
     * Delete transients from `options` table.
     *
     * @return void
     */
    public function deleteTransientsFromOptions()
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_%',
                '_site_transient_%'
            )
        );
    }

    /**
     * Delete transients from `sitemeta` table.
     *
     * @return void
     */
    public function deleteTransientsFromSiteMeta()
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s",
                '_site_transient_%'
            )
        );
    }

    /**
     * Delete transients from all site's `options` tables.
     *
     * @return void
     */
    public function deleteTransientsFromSites()
    {
        global $wpdb;

        $siteIds = get_sites([
            'fields' => 'ids',
            'number' => 10000,
        ]);

        foreach ($siteIds as $id) {
            $prefix = $wpdb->get_blog_prefix($id);

            $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM {$prefix}options WHERE option_name LIKE %s",
                    '_transient_%'
                )
            );
        }
    }
}
