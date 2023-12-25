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

namespace RedisCachePro\Plugin;

use RedisCachePro\Plugin;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Authorization
{
    /**
     * Boot authorization component.
     *
     * @return void
     */
    public function bootAuthorization()
    {
        add_filter('user_has_cap', [$this, 'userHasCapability'], 10, 3);

        if (function_exists('\members_plugin')) {
            $this->registerMembersIntegration();
        }

        if (class_exists('\WP_User_Manager')) {
            $this->registerUserManagerIntegration();
        }

        if (class_exists('\User_Role_Editor')) {
            $this->registerUreIntegration();
        }

        if (defined('\CAPSMAN_VERSION')) {
            $this->registerCmeIntegration();
        }
    }

    /**
     * Whether the given user has the `objectcache_manage` capability.
     *
     * Falls back to the `install_plugins` capability.
     *
     * @param  bool[]  $allcaps
     * @param  string[]  $caps
     * @param  array<mixed>  $args
     * @return array<string, bool>
     */
    public function userHasCapability($allcaps, $caps, $args)
    {
        if ($args[0] !== Plugin::Capability) {
            return $allcaps;
        }

        if (array_key_exists(Plugin::Capability, $allcaps)) {
            return $allcaps;
        }

        if (! empty($allcaps['install_plugins'])) {
            $allcaps[Plugin::Capability] = true;
        }

        return $allcaps;
    }

    /**
     * Register capabilities and groups with the Members plugin.
     *
     * @link https://wordpress.org/plugins/members/
     *
     * @return void
     */
    protected function registerMembersIntegration()
    {
        if (! function_exists('\members_register_caps')) {
            return;
        }

        add_action('members_register_caps', function () {
            members_register_cap(Plugin::Capability, [
                'label' => 'Manage Object Cache',
                'group' => 'objectcache',
            ]);
        });

        if (! function_exists('\members_register_cap_groups')) {
            return;
        }

        add_action('members_register_cap_groups', function () {
            members_register_cap_group('objectcache', [
                'label' => 'Object Cache Pro',
                'caps' => [Plugin::Capability],
                'icon' => 'dashicons-database',
                'priority' => 30,
            ]);
        });
    }

    /**
     * Register capabilities and groups with the Members plugin.
     *
     * @link https://wordpress.org/plugins/wp-user-manager/
     *
     * @return void
     */
    protected function registerUserManagerIntegration()
    {
        if (! function_exists('\wpum_register_cap')) {
            return;
        }

        add_action('wpum_register_caps', function () {
            wpum_register_cap(Plugin::Capability, [
                'label' => 'Manage Object Cache',
                'group' => 'objectcache',
            ]);
        });

        if (! function_exists('\wpum_register_cap_group')) {
            return;
        }

        add_action('wpum_register_cap_groups', function () {
            wpum_register_cap_group('objectcache', [
                'label' => 'Object Cache Pro',
                'caps' => [Plugin::Capability],
                'icon' => 'dashicons-database',
                'priority' => 30,
            ]);
        });
    }

    /**
     * Register capabilities and groups with the User Role Editor plugin.
     *
     * @link https://en-ca.wordpress.org/plugins/user-role-editor/
     *
     * @return void
     */
    protected function registerUreIntegration()
    {
        add_filter('ure_capabilities_groups_tree', function ($groups) {
            return array_merge($groups, ['objectcache' => [
                'caption' => 'Object Cache Pro',
                'parent' => 'custom',
                'level' => 2,
            ]]);
        });

        add_filter('ure_custom_capability_groups', function ($groups, $cap_id) {
            if ($cap_id === Plugin::Capability) {
                $groups[] = 'objectcache';
            }

            return $groups;
        }, 10, 2);

        add_filter('ure_full_capabilites', function ($caps) { // that typo ¯\_(ツ)_/¯
            if (! array_key_exists(Plugin::Capability, $caps)) {
                $caps[Plugin::Capability] = [
                    'inner' => Plugin::Capability,
                    'human' => 'Manage Object Cache',
                    'wp_core' => false,
                ];
            }

            return $caps;
        });
    }

    /**
     * Register capabilities and groups with the PublishPress Capabilities plugin.
     *
     * @link https://en-ca.wordpress.org/plugins/capability-manager-enhanced/
     *
     * @return void
     */
    protected function registerCmeIntegration()
    {
        add_filter('cme_plugin_capabilities', function ($plugin_caps) {
            return array_merge($plugin_caps, [
                'Object Cache Pro' => [Plugin::Capability],
            ]);
        });
    }
}

/**
 * Creates a cryptographic token tied to a specific action and window of time.
 *
 * @param  string|int  $action
 * @return string
 */
function wp_create_nonce($action = -1)
{
    $i = ceil(time() / (DAY_IN_SECONDS / 2));

    return substr(wp_hash("{$i}|{$action}", 'nonce'), -12, 10);
}

/**
 * Verifies that a correct security nonce was used with time limit.
 *
 * A nonce is valid for 24 hours.
 *
 * @param  string  $nonce
 * @param  string|int  $action
 * @return int|false
 */
function wp_verify_nonce($nonce, $action = -1)
{
    $nonce = sprintf('%010x', $nonce);
    $action = strrev((string) $action);

    if (empty($nonce)) {
        return false;
    }

    $i = ceil(time() / (DAY_IN_SECONDS / 2));

    // nonce generated 0-12 hours ago
    if (hash_equals(substr(wp_hash("{$i}|{$action}", 'nonce'), -12, 10), $nonce)) {
        return 1;
    }

    $i--;

    // nonce generated 12-24 hours ago
    if (hash_equals(substr(wp_hash("{$i}|{$action}", 'nonce'), -12, 10), $nonce)) {
        return 2;
    }

    return false;
}
