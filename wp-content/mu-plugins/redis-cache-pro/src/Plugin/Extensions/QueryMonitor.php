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

namespace RedisCachePro\Plugin\Extensions;

use QM_Collectors;

use RedisCachePro\Diagnostics\Diagnostics;
use RedisCachePro\Extensions\QueryMonitor\CommandsCollector;
use RedisCachePro\Extensions\QueryMonitor\CommandsHtmlOutput;
use RedisCachePro\Extensions\QueryMonitor\ObjectCacheCollector;
use RedisCachePro\Extensions\QueryMonitor\ObjectCacheHtmlOutput;

trait QueryMonitor
{
    /**
     * Boot Query Monitor component and register panels.
     *
     * @return void
     */
    public function bootQueryMonitor()
    {
        if (! class_exists('QM_Collectors')) {
            return;
        }

        add_action('init', [$this, 'registerQmCollectors']);
        add_action('qm/constants', [$this, 'registerQmConstants']);

        add_filter('qm/outputter/html', [$this, 'registerQmOutputters']);

        add_filter('qm/component_type/unknown', [$this, 'fixUnknownQmComponentType'], 10, 2);

        add_filter('qm/component_name/plugin', [$this, 'fixUnknownQmComponentName'], 10, 2);
        add_filter('qm/component_name/mu-plugin', [$this, 'fixUnknownQmComponentName'], 10, 2);

        add_filter('qm/component_context/plugin', [$this, 'fixUnknownQmComponentContext'], 10, 2);
        add_filter('qm/component_context/mu-plugin', [$this, 'fixUnknownQmComponentContext'], 10, 2);
    }

    /**
     * Registers all object cache related Query Monitor constants.
     *
     * @param  array<string, array<string, mixed>>  $constants
     * @return array<string, array<string, mixed>>
     */
    public function registerQmConstants($constants)
    {
        $constants['QM_OBJECTCACHE_EXPENSIVE'] = [
            'label' => 'If an individual cache command takes longer than this time to execute, it’s considered "slow" and triggers a warning.',
            'default' => 0.005,
        ];

        $constants['QM_OBJECTCACHE_HEAVY'] = [
            'label' => 'If an individual cache key is larger than this in bytes, it’s considered "heavy" and triggers a warning.',
            'default' => 1024 * 1024,
        ];

        return $constants;
    }

    /**
     * Registers all object cache related Query Monitor collectors.
     *
     * @return void
     */
    public function registerQmCollectors()
    {
        if (! class_exists('QM_Collector')) {
            return;
        }

        require_once "{$this->directory}/src/Extensions/QueryMonitor/ObjectCacheCollector.php";
        require_once "{$this->directory}/src/Extensions/QueryMonitor/CommandsCollector.php";

        QM_Collectors::add(new ObjectCacheCollector);
        QM_Collectors::add(new CommandsCollector);
    }

    /**
     * Registers all object cache related Query Monitor HTML outputters.
     *
     * @param  array<string, mixed>  $output
     * @return array<string, mixed>|void
     */
    public function registerQmOutputters(array $output)
    {
        if (! class_exists('QM_Output_Html')) {
            return;
        }

        // Added in Query Monitor 3.1.0
        if (! method_exists('QM_Output_Html', 'before_non_tabular_output')) { // @phpstan-ignore-line
            return;
        }

        require_once "{$this->directory}/src/Extensions/QueryMonitor/ObjectCacheHtmlOutput.php";
        require_once "{$this->directory}/src/Extensions/QueryMonitor/CommandsHtmlOutput.php";

        $output['cache'] = new ObjectCacheHtmlOutput(
            QM_Collectors::get('cache')
        );

        $output['cache_log'] = new CommandsHtmlOutput(
            QM_Collectors::get('cache-commands')
        );

        return $output;
    }

    /**
     * Fix unknown Query Monitor component type.
     *
     * @param  string  $type
     * @param  string  $file
     * @return string
     */
    public function fixUnknownQmComponentType($type, $file)
    {
        if (strpos($file, $this->directory) !== false) {
            return Diagnostics::isMustUse() ? 'mu-plugin' : 'plugin';
        }

        return $type;
    }

    /**
     * Fix unknown Query Monitor component name.
     *
     * @param  string  $name
     * @param  string  $file
     * @return string
     */
    public function fixUnknownQmComponentName($name, $file)
    {
        if (strpos($file, $this->directory) === false) {
            return $name;
        }

        if (Diagnostics::isMustUse()) {
            return sprintf(__('MU Plugin: %s', 'query-monitor'), $this->slug());
        }

        return sprintf(__('Plugin: %s', 'query-monitor'), $this->slug());
    }

    /**
     * Fix unknown Query Monitor component context.
     *
     * @param  string  $context
     * @param  string  $file
     * @return string
     */
    public function fixUnknownQmComponentContext($context, $file)
    {
        if (strpos($file, $this->directory) === false) {
            return $context;
        }

        return $this->slug();
    }
}
