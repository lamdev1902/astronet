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

namespace RedisCachePro\ObjectCaches\Concerns;

use Throwable;

use RedisCachePro\Configuration\Configuration;

/**
 * This is an experimental feature and not supported officially WordPress.
 *
 * In multisite environments WordPress has no mechanism to flush an individual
 * blog (site) and will always flush the entire network, which is inefficient.
 *
 * Settings the `network_flush` configuration option to `global`, will cause
 * Object Cache Pro to only flush the current blog's data and all global groups.
 *
 * Settings the `network_flush` configuration option to `site`, will cause
 * Object Cache Pro to only flush the current blog's data.
 */
trait FlushesNetworks
{
    /**
     * Removes all cache items for an individual blog in multisite environments.
     *
     * The `network_flush` configuration option will be used,
     * if `$network_flush` parameter is not given.
     *
     * @param  int|null  $siteId
     * @param  string|null  $network_flush
     * @return bool
     */
    public function flushBlog(int $siteId = null, string $network_flush = null): bool
    {
        if (is_null($siteId)) {
            $siteId = $this->blogId;
        }

        if (is_null($network_flush)) {
            $network_flush = $this->config->network_flush;
        }

        $originalBlogId = $this->blogId;
        $this->blogId = $siteId;

        $patterns = [
            preg_replace('/:{?deadf00d}?/', '', (string) $this->id('*', dechex(3735941133))),
        ];

        if ($network_flush === Configuration::NETWORK_FLUSH_GLOBAL) {
            array_push($patterns, ...array_map(function ($group) {
                return $this->id('*', $group);
            }, $this->globalGroups()));
        }

        $this->blogId = $originalBlogId;

        try {
            $this->deleteByPattern(array_filter($patterns));
        } catch (Throwable $exception) {
            $this->error($exception);

            return false;
        }

        return parent::flushBlog($siteId, $network_flush);
    }
}
