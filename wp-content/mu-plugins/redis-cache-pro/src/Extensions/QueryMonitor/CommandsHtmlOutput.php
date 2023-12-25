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

use QM_Output_Html;

class CommandsHtmlOutput extends QM_Output_Html
{
    /**
     * Creates a new instance.
     *
     * @param  \QM_Collector  $collector
     */
    public function __construct($collector)
    {
        parent::__construct($collector);

        add_filter('qm/output/panel_menus', function ($menu) {
            $menu['qm-cache']['children'][] = $this->menu(['title' => $this->name()]);

            return $menu;
        });
    }

    /**
     * Returns the name of the QM outputter.
     *
     * @return string
     */
    public function name()
    {
        return 'Commands';
    }

    /**
     * Prints the QM panel's content.
     *
     * @return void
     */
    public function output()
    {
        $data = $this->collector->get_data();

        if (! isset($data['commands'])) {
            $this->before_non_tabular_output();

            echo $this->build_notice(
                'The current object cache drop-in does not support listing commands.'
            );

            $this->after_non_tabular_output();

            return;
        }

        if (empty($data['commands'])) {
            $this->before_non_tabular_output();

            echo $this->build_notice(
                (string) file_get_contents(__DIR__ . '/templates/no-log.phtml')
            );

            $this->after_non_tabular_output();

            return;
        }

        require __DIR__ . '/templates/commands.phtml';
    }
}
