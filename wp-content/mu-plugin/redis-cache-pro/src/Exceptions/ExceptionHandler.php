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

use Throwable;

use RedisCachePro\Configuration\Configuration;

class ExceptionHandler
{
    /**
     * Render the error screen and die.
     *
     * @param Configuration $config
     *
     * @return void
     */
    public static function render(Configuration $config, Throwable $exception): void
    {
        if (file_exists(WP_CONTENT_DIR . '/redis-error.php')) {
            require_once WP_CONTENT_DIR . '/redis-error.php';
            exit;
        }

        $message = [];
        $message[] = '<h1>Error establishing a Redis connection</h1>';

        if ($config->debug || wp_installing()) {
            $message[] = "<p><code>{$exception->getMessage()}</code></p>";
            $message[] = '<p>This means that the Redis server is unreachable, or that the <code>WP_REDIS_CONFIG</code> constant in your <code>wp-config.php</code> file is incorrectly configured.</p>';
            $message[] = '<ul>';
            $message[] = '  <li>Is Redis server up and running?</li>';
            $message[] = '  <li>Is the correct <code>host</code> and <code>port</code> set?</li>';
            $message[] = '  <li>Is the <code>WP_REDIS_CONFIG</code> defined at the top of the <code>wp-config.php</code>?</li>';
            $message[] = '</ul>';
            $message[] = '<p>For more information, please read the <a href="https://objectcache.pro/docs/installation">installation instructions</a>.</p>';
        }

        wp_die(implode("\n", $message));
    }
}
