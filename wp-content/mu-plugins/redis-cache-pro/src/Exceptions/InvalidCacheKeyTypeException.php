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

namespace RedisCachePro\Exceptions;

class InvalidCacheKeyTypeException extends ObjectCacheException
{
    /**
     * Creates an "invalid cache key value/type" exception for given key.
     *
     * @param  mixed  $key
     * @return InvalidCacheKeyTypeException
     */
    public static function forKey($key)
    {
        $type = strtolower(gettype($key));

        if ($type === 'string' && trim($key) === '') {
            $type = 'empty string';
        }

        /** @var string $backtrace */
        $backtrace = function_exists('wp_debug_backtrace_summary')
            ? wp_debug_backtrace_summary(__CLASS__, 4)
            : 'backtrace unavailable';

        if (strpos($backtrace, ', wp_cache_')) {
            $backtrace = strstr($backtrace, ', wp_cache_', true);
        }

        return new static( // @phpstan-ignore-line
            "Cache key must be integer or non-empty string, {$type} given ({$backtrace})"
        );
    }
}
