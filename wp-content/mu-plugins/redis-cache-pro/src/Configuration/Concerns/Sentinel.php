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

namespace RedisCachePro\Configuration\Concerns;

use RedisCachePro\Exceptions\ConfigurationException;
use RedisCachePro\Exceptions\ConfigurationInvalidException;

trait Sentinel
{
    /**
     * The array of Redis Sentinels.
     *
     * @var array<string>
     */
    protected $sentinels;

    /**
     * The Redis Sentinel service name.
     *
     * @var ?string
     */
    protected $service;

    /**
     * Set the array of Redis Sentinels.
     *
     * @param  array<string>  $sentinels
     * @return void
     */
    public function setSentinels($sentinels)
    {
        if (! \is_array($sentinels)) {
            throw new ConfigurationException('`sentinels` must an array of Redis Sentinels');
        }

        if (empty($sentinels)) {
            throw new ConfigurationInvalidException('`sentinels` must be a non-empty array');
        }

        $this->sentinels = $sentinels;
    }

    /**
     * Set the connection protocol.
     *
     * @param  string  $service
     * @return void
     */
    public function setService($service)
    {
        if (! \is_string($service)) {
            throw new ConfigurationInvalidException('`service` must be a string');
        }

        $this->service = $service;
    }
}
