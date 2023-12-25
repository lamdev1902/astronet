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

class InvalidDatabaseException extends ConfigurationException
{
    /**
     * Creates a new exception.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  \Throwable|null  $previous
     * @return void
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        if (empty($message)) {
            $message = 'unknown';
        }

        $message = implode(' ', [
            "The Redis database index `$message` does not exist.",
            'Try increasing the `databases` configuration directive in your `redis.conf` file.',
            'For more information see: https://redis.io/docs/management/config-file/',
        ]);

        parent::__construct($message, $code, $previous);
    }
}
