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

namespace RedisCachePro\Connectors\Concerns;

use RedisCachePro\Configuration\Configuration;

trait HandlesBackoff
{
    /**
     * Returns the next delay for the given retry.
     *
     * @param  \RedisCachePro\Configuration\Configuration  $config
     * @param  int  $retries
     * @return int
     */
    public static function nextDelay(Configuration $config, int $retries)
    {
        if ($config->backoff === Configuration::BACKOFF_NONE) {
            return $retries ** 2;
        }

        $retryInterval = $config->retry_interval;
        $jitter = $retryInterval * 0.1;

        return $retries * \mt_rand(
            (int) \floor($retryInterval - $jitter),
            (int) \ceil($retryInterval + $jitter)
        );
    }
}
