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

namespace RedisCachePro\Extensions\QueryMonitor;

use QM_Data;
use QM_Collector;
use QM_Data_Cache;

use RedisCachePro\Loggers\ArrayLogger;
use RedisCachePro\ObjectCaches\ObjectCache;

class ObjectCacheCollector extends QM_Collector
{
    /**
     * Holds the ID of the collector.
     *
     * @var string
     */
    public $id = 'cache';

    /**
     * @var array<string, mixed>|\QM_Data_Cache
     */
    protected $data;

    /**
     * Returns the collector name.
     *
     * Obsolete since Query Monitor 3.5.0.
     *
     * @return string
     */
    public function name()
    {
        return 'Object Cache';
    }

    /**
     * Use correct QM storage class.
     *
     * @return QM_Data_Cache
     */
    public function get_storage(): QM_Data
    {
        return new QM_Data_Cache;
    }

    /**
     * Populate the `data` property.
     *
     * @return void
     */
    public function process()
    {
        global $wp_object_cache, $timestart;

        $this->process_defaults();

        $diagnostics = $GLOBALS['ObjectCachePro']->diagnostics();

        $dropinExists = $diagnostics->dropinExists();
        $dropinIsValid = $dropinExists && $diagnostics->dropinIsValid();

        $this->data['has-dropin'] = $dropinExists;
        $this->data['valid-dropin'] = $dropinIsValid;

        $this->data['license'] = $GLOBALS['ObjectCachePro']->license();

        if (! $dropinIsValid) {
            return;
        }

        $this->data['status'] = $diagnostics['general']['status']->html;

        if (! $wp_object_cache instanceof ObjectCache) {
            return;
        }

        $info = $wp_object_cache->info();

        $this->data['errors'] = $info->errors;
        $this->data['meta'] = $info->meta;
        $this->data['groups'] = $info->groups;

        $metrics = $wp_object_cache->metrics();

        $this->data['hits'] = $metrics->hits;
        $this->data['misses'] = $metrics->misses;
        $this->data['ratio'] = $metrics->hitRatio;
        $this->data['memory'] = $metrics->memory;
        $this->data['cache'] = $metrics->groups;

        $requestMs = (microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? $timestart)) * 1000;

        $waitRatio = ($metrics->storeWait / $requestMs) * 100;

        if ($wp_object_cache->connection()) {
            $this->data['prefetches'] = $metrics->prefetches;
            $this->data['store_reads'] = $metrics->storeReads;
            $this->data['store_writes'] = $metrics->storeWrites;
            $this->data['store_hits'] = $metrics->storeHits;
            $this->data['store_misses'] = $metrics->storeMisses;

            $this->data['ms_request'] = round($requestMs, 2);
            $this->data['ms_cache'] = round($metrics->storeWait, 2);
            $this->data['ms_cache_median'] = round($metrics->storeWaitAverage, 2);
            $this->data['ms_cache_ratio'] = round($waitRatio, $waitRatio < 1 ? 3 : 1);
        }

        // Used by QM itself
        $this->data['cache_hit_percentage'] = $metrics->hitRatio;

        if ($this->data instanceof QM_Data_Cache) {
            $this->data->stats['cache_hits'] = $metrics->hits;
            $this->data->stats['cache_misses'] = $metrics->misses;
        } else {
            $this->data['stats']['cache_hits'] = $metrics->hits;
            $this->data['stats']['cache_misses'] = $metrics->misses;
        }

        $logger = $wp_object_cache->logger();

        if (! $logger instanceof ArrayLogger) {
            return;
        }

        $this->data['commands'] = count(array_filter($logger->messages(), static function ($message) {
            return isset($message['context']['command']);
        }));
    }

    /**
     * Adds required default values to the `data` property.
     *
     * @return void
     */
    public function process_defaults()
    {
        $this->data['status'] = 'Unknown';
        $this->data['ratio'] = 0;
        $this->data['hits'] = 0;
        $this->data['misses'] = 0;
        $this->data['memory'] = 0;

        // Used by QM itself
        $this->data['object_cache_extensions'] = [];
        $this->data['opcode_cache_extensions'] = [];

        if (function_exists('extension_loaded')) {
            $this->data['object_cache_extensions'] = array_map('extension_loaded', [
                'APCu' => 'APCu',
                'Memcache' => 'Memcache',
                'Memcached' => 'Memcached',
                'Redis' => 'Redis',
            ]);

            $this->data['opcode_cache_extensions'] = array_map('extension_loaded', [
                'APC' => 'APC',
                'Zend OPcache' => 'Zend OPcache',
            ]);
        }

        $this->data['has_object_cache'] = (bool) wp_using_ext_object_cache();
        $this->data['has_opcode_cache'] = array_filter($this->data['opcode_cache_extensions']) ? true : false;

        $this->data['display_hit_rate_warning'] = false;
        $this->data['ext_object_cache'] = $this->data['has_object_cache'];
    }
}
