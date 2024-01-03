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

namespace RedisCachePro\Loggers;

/**
 * @deprecated 1.18.0
 * @see \RedisCachePro\Loggers\CallbackLogger
 */
class BacktraceLogger extends Logger
{
    /**
     * Logs with an arbitrary level.
     *
     * @deprecated 1.18.0
     * @see \RedisCachePro\Loggers\CallbackLogger
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  array<mixed>  $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        \error_log(
            "objectcache.{$level}: {$message} [Backtrace not available]"
        );
    }
}
