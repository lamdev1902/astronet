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

class ArrayLogger extends ErrorLogLogger
{
    /**
     * Holds all logged messages.
     *
     * @var array<int, array<string, mixed>>
     */
    protected $messages = [];

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed  $level
     * @param  string  $message
     * @param  array<mixed>  $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->messages[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        parent::log($level, $message, $context);
    }

    /**
     * Return all logged messages as array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function messages(): array
    {
        return $this->messages;
    }
}
