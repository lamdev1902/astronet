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

namespace RedisCachePro\Connections\Concerns;

use Throwable;

use RedisCachePro\Exceptions\ConnectionException;

trait SentinelsConnection
{
    /**
     * Returns the current Sentinel's URL.
     *
     * @return string
     */
    public function sentinelUrl()
    {
        return $this->sentinel;
    }

    /**
     * Set the pool for read commands.
     *
     * @return void
     */
    protected function setPool()
    {
        $this->pool = $this->replicas;
    }

    /**
     * Connect to the first available Sentinel.
     *
     * @return void
     */
    protected function connectToSentinels()
    {
        if ($this->sentinel) {
            $this->sentinels[$this->sentinel] = false;
        }

        foreach ($this->sentinels as $url => $state) {
            unset($this->sentinel, $this->primary, $this->replicas, $this->pool);

            if (! is_null($state)) {
                continue;
            }

            try {
                $this->sentinel = $url;
                $this->establishConnections($url);
                $this->setPool();

                return;
            } catch (Throwable $error) {
                $this->sentinels[$url] = false;

                if ($this->config->debug) {
                    error_log("objectcache.notice: {$error->getMessage()}");
                }
            }
        }

        $lastMessage = isset($error) ? "[{$error->getMessage()}]" : '';

        throw new ConnectionException("Unable to connect to any valid sentinels {$lastMessage}", 0, $error ?? null);
    }

    /**
     * Run a command against Redis Sentinel.
     *
     * @param  string  $name
     * @param  array<mixed>  $parameters
     * @return mixed
     */
    public function command(string $name, array $parameters = [])
    {
        $this->lastCommand = null;

        $isReading = \in_array(\strtoupper($name), $this->readonly);

        // send `alloptions` read requests to the primary node
        if ($isReading && \is_string($parameters[0] ?? null)) {
            $isReading = \strpos($parameters[0], 'options:alloptions') === false;
        }

        $node = $isReading
            ? $this->pool[\array_rand($this->pool)]
            : $this->primary;

        try {
            $result = $node->command($name, $parameters);

            $this->lastCommand = $node->lastCommand;

            return $result;
        } catch (Throwable $th) {
            try {
                $this->connectToSentinels();
            } catch (ConnectionException $ex) {
                throw new ConnectionException($ex->getMessage(), $ex->getCode(), $th);
            }
        }

        $result = $node->command($name, $parameters);

        $this->lastCommand = $node->lastCommand;

        return $result;
    }
}
