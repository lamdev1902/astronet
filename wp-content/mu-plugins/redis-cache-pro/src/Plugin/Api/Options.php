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
use RedisCachePro\License;

use RedisCachePro\Plugin\Options\Sanitizer;
use RedisCachePro\Plugin\Options\Validator;

class Options extends WP_REST_Controller
{
    /**
     * The plugin instance.
     *
     * @var \RedisCachePro\Plugin
     */
    protected $plugin;

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
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $this->namespace = 'objectcache/v1';
        $this->resource_name = 'options';
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
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::READABLE),
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
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
         * @param  string  $capability  The capability name.
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
    public function get_item($request)
    {
        $options = $this->plugin->options();

        /** @var \WP_REST_Response $response */
        $response = rest_ensure_response($options);
        $response->header('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Returns the REST API response for the request.
     *
     * @param  \WP_REST_Request  $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_item($request)
    {
        $options = $request->get_params();

        if (array_key_exists('objectcache_options', $options)) {
            $options = $options['objectcache_options'];
        }

        $filteredOptions = array_filter((array) $options, function ($name) {
            return array_key_exists($name, $this->plugin->defaultOptions());
        }, ARRAY_FILTER_USE_KEY);

        $sanitizer = new Sanitizer;
        $sanitizedOptions = [];

        foreach ($filteredOptions as $name => $value) {
            $sanitizedOptions[$name] = $sanitizer->{$name}($value);
        }

        $validator = new Validator($this->plugin);

        $result = $validator->validate($sanitizedOptions);

        if (is_wp_error($result)) {
            return $result;
        }

        update_site_option('objectcache_options', array_merge($this->plugin->options(), $sanitizedOptions));

        return $this->get_item($request);
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
            'title' => 'objectcache_options',
            'type' => 'object',
            'properties' => [
                'channel' => [
                    'description' => 'The update channel acts as a "minimum stability", meaning that using the Alpha channel will also show the latest Beta releases and so on, whichever has the highest version number. Using an update channel other than Stable may break your site.',
                    'type' => 'string',
                    'enum' => array_keys(License::Stabilities),
                ],
                'flushlog' => [
                    'description' => 'Whether to keep a log of cache flushes. Ignored when debug mode is enabled.',
                    'type' => 'boolean',
                ],
            ],
        ];

        $this->schema = $schema;

        return $this->add_additional_fields_schema($this->schema);
    }
}
