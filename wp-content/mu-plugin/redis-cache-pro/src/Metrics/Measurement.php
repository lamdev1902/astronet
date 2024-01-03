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

namespace RedisCachePro\Metrics;

class Measurement
{
    /**
     * The unique identifier of the measurement.
     *
     * @var string
     */
    public $id;

    /**
     * The Unix timestamp with microseconds of the measurement.
     *
     * @var float
     */
    public $timestamp;

    /**
     * The hostname on which the measurement was taken.
     *
     * @var string|null
     */
    public $hostname;

    /**
     * The URL path of the request, if applicable.
     *
     * @var string
     */
    public $path;

    /**
     * The WordPress measurement.
     *
     * @var \RedisCachePro\Metrics\WordPressMetrics
     */
    public $wp;

    /**
     * The Redis measurement.
     *
     * @var \RedisCachePro\Metrics\RedisMetrics|null
     */
    public $redis;

    /**
     * The Relay measurement.
     *
     * @var \RedisCachePro\Metrics\RelayMetrics|null
     */
    public $relay;

    /**
     * Makes a new instance.
     *
     * @return self
     */
    public static function make()
    {
        $self = new self;

        $self->id = substr(md5(uniqid((string) mt_rand(), true)), 12);
        $self->timestamp = microtime(true);
        $self->hostname = gethostname() ?: null;

        $self->path = $_SERVER['REQUEST_URI'] ?? null;

        if (isset($_ENV['DYNO'])) {
            $self->hostname = $_ENV['DYNO']; // Heroku
        }

        return $self;
    }

    /**
     * Returns an rfc3339 compatible timestamp.
     *
     * @return string
     */
    public function rfc3339()
    {
        return substr_replace(
            date('c', intval($this->timestamp)),
            substr((string) fmod($this->timestamp, 1), 1, 7),
            19,
            0
        );
    }

    /**
     * Returns the measurement as array.
     *
     * @return array<mixed>
     */
    public function toArray()
    {
        $array = $this->wp->toArray();

        if ($this->redis) {
            $redis = $this->redis->toArray();

            $array += array_combine(array_map(static function ($key) {
                return "redis-{$key}";
            }, array_keys($redis)), $redis);
        }

        if ($this->relay) {
            $relay = $this->relay->toArray();

            $array += array_combine(array_map(static function ($key) {
                return "relay-{$key}";
            }, array_keys($relay)), $relay);
        }

        return $array;
    }

    /**
     * Returns the request metrics in string format.
     *
     * @return string
     */
    public function __toString()
    {
        return implode(' ', array_filter([
            $this->wp,
            $this->redis ? (string) $this->redis : null,
            $this->relay ? (string) $this->relay : null,
        ]));
    }

    /**
     * Helper method to access metrics.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (strpos($name, '->') !== false) {
            [$type, $metric] = explode('->', $name);

            if (strpos($metric, '-') !== false) {
                $metric = lcfirst(str_replace('-', '', ucwords($metric, '-')));
            }

            if (property_exists($this, $type)) {
                return $this->{$type}->{$metric} ?? null;
            }
        }

        trigger_error(
            sprintf('Undefined property: %s::$%s', get_called_class(), $name),
            E_USER_WARNING
        );
    }
}
