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

namespace RedisCachePro\Plugin\Options;

class Sanitizer
{
    /**
     * Sanitize the `channel` option value.
     *
     * @param  mixed  $value
     * @return string
     */
    public function channel($value)
    {
        return sanitize_key($value);
    }

    /**
     * Sanitize the `flushlog` option value.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function flushlog($value)
    {
        return (bool) intval($value);
    }

    /**
     * Sanitize the `groupflushlog` option value.
     *
     * @param  mixed  $value
     * @return bool
     */
    public function groupflushlog($value)
    {
        return (bool) intval($value);
    }
}
