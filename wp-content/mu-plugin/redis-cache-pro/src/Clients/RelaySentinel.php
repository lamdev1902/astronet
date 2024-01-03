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

namespace RedisCachePro\Clients;

use Relay\Sentinel;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;

/**
 * @mixin \Relay\Sentinel
 * @property \Relay\Sentinel $client
 */
class RelaySentinel extends Client
{
    /**
     * Creates an OpenTelemetry tracer from given tracer provider.
     *
     * @param  TracerProviderInterface  $tracerProvider
     * @return TracerInterface
     */
    protected function createOpenTelemetryTracer(TracerProviderInterface $tracerProvider): TracerInterface
    {
        return $tracerProvider->getTracer(Sentinel::class, (string) \phpversion('relay'));
    }
}
