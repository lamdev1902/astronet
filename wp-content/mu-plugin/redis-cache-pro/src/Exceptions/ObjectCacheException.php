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

use Exception;
use Throwable;

class ObjectCacheException extends Exception
{
    /**
     * Creates a new exception from the given exception.
     *
     * @param  \Throwable  $exception
     * @return self
     */
    public static function from(Throwable $exception)
    {
        if ($exception instanceof self) {
            return $exception;
        }

        return new static( // @phpstan-ignore-line
            $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }
}
