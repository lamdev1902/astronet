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

namespace RedisCachePro\Plugin\Api;

use WP_Error;
use WP_REST_Server;
use WP_REST_Controller;

use RedisCachePro\Plugin;
use RedisCachePro\ObjectCaches\ObjectCacheInterface;

class Groups extends WP_REST_Controller
{
    /**
     * The resource name of this controller's route.
     *
     * @var string
     */
    protected $resource_name;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->namespace = 'objectcache/v1';
        $this->resource_name = 'groups';
    }

    /**
     * Register all REST API routes.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, "/{$this->resource_name}", [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'item_permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::DELETABLE),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * The permission callback for the endpoint.
     *
     * @param  \WP_REST_Request  $request
     * @return true|\WP_Error
     */
    public function item_permissions_check($request)
    {
        /**
         * Filter the capability required to access REST API endpoints.
         *
         * @param  string  $capability  The drop-in metadata.
         */
        $capability = (string) apply_filters('objectcache_rest_capability', Plugin::Capability);

        if (current_user_can($capability)) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            'Sorry, you are not allowed to do that.',
            ['status' => rest_authorization_required_code()]
        );
    }

    /**
     * Returns the REST API response for the request.
     *
     * @param  \WP_REST_Request  $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_items($request)
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCacheInterface) {
            return new WP_Error(
                'objectcache_not_supported',
                'The object cache is not supported.',
                ['status' => 400]
            );
        }

        $config = $wp_object_cache->config();
        $connection = $wp_object_cache->connection();

        if (! $connection) {
            return new WP_Error(
                'objectcache_not_connected',
                'The object cache is not connected.',
                ['status' => 400]
            );
        }

        if (! method_exists($connection, 'listKeys')) {
            return new WP_Error(
                'objectcache_not_supported',
                'The object cache connection is unsupported.',
                ['status' => 400]
            );
        }

        $prefix = $config->prefix;
        $pattern = is_null($prefix) ? null : "{$prefix}:*";

        $groups = [];

        foreach ($connection->listKeys($pattern) as $keys) {
            foreach ($keys as $key) {
                $groups[$this->parseGroup($key)][] = $key;
            }
        }

        $groups = $this->prepareGroupsForResponse(array_map('count', $groups));

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response($groups);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Returns the REST API response for the request.
     *
     * @param  \WP_REST_Request  $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item($request)
    {
        global $wp_object_cache;

        if (! $wp_object_cache instanceof ObjectCacheInterface) {
            return new WP_Error(
                'objectcache_not_supported',
                'The object cache is not supported.',
                ['status' => 400]
            );
        }

        if (! $wp_object_cache->connection()) {
            return new WP_Error(
                'objectcache_not_connected',
                'The object cache is not connected.',
                ['status' => 400]
            );
        }

        $group = $request->get_param('group');

        if (! $group) {
            return new WP_Error(
                'no_group_provided',
                'No cache group was provided.',
                ['status' => 400]
            );
        }

        wp_cache_flush_group($group);

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response(true);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Returns the key's group name.
     *
     * @param  string  $id
     * @return string
     */
    protected function parseGroup(string $id)
    {
        if (! strpos($id, ':')) {
            return '__ungrouped__';
        }

        if (strpos('options:alloptions:', $id) !== false) {
            $id = str_replace('options:alloptions:', 'options:alloptions-', $id);
        }

        return array_reverse(
            explode(':', $id)
        )[1];
    }

    /**
     * Transform the groups into the response format.
     *
     * @param  array<mixed>  $groups
     * @return array<array<string, mixed>>
     */
    protected function prepareGroupsForResponse(array $groups)
    {
        array_walk($groups, static function (&$item, $group) {
            $item = [
                'group' => str_replace(['{', '}'], '', (string) $group),
                'count' => $item,
            ];
        });

        $groups = array_values($groups);

        usort($groups, static function ($a, $b) {
            return strcmp($a['group'], $b['group']);
        });

        return $groups;
    }

    /**
     * Retrieves the endpoint's schema, conforming to JSON Schema.
     *
     * @return array<string, mixed>
     */
    public function get_item_schema()
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'objectcache_groups',
            'type' => 'object',
            'properties' => [
                'group' => [
                    'description' => 'The cache group name.',
                    'type' => 'string',
                ],
                'count' => [
                    'description' => 'The count of the group keys.',
                    'type' => 'integer',
                ],
            ],
        ];

        $this->schema = $schema;

        return $this->add_additional_fields_schema($this->schema);
    }
}
