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

use WP_Error;
use Throwable;

use Relay\Relay;

use RedisCachePro\Plugin;
use RedisCachePro\License;
use RedisCachePro\ObjectCaches\ObjectCache;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Licensing
{
    /**
     * Boot licensing component.
     *
     * @return void
     */
    public function bootLicensing()
    {
        add_action('admin_notices', [$this, 'displayLicenseNotices'], -1);
        add_action('network_admin_notices', [$this, 'displayLicenseNotices'], -1);

        if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
            $this->storeRelayLicense();
        }
    }

    /**
     * Return the license configured token.
     *
     * @return string|void
     */
    public function token()
    {
        if ($this->lazyAssConfig() || ! defined('\WP_REDIS_CONFIG')) {
            return;
        }

        if (isset(\WP_REDIS_CONFIG['token'])) {
            return \WP_REDIS_CONFIG['token'];
        }
    }

    /**
     * Display admin notices when license is unpaid/canceled,
     * and when no license token is set.
     *
     * @return void
     */
    public function displayLicenseNotices()
    {
        if (! defined('\WP_REDIS_CONFIG')) {
            return;
        }

        if (! current_user_can('activate_plugins')) {
            return;
        }

        $screen = get_current_screen();

        if (in_array($screen->id ?? null, ['site-health', 'options-privacy'])) {
            return;
        }

        $notice = function ($type, $text) {
            printf('<div class="notice notice-%s"><p>%s</p></div>', $type, $text);
        };

        $license = $this->license();

        if ($license->isCanceled()) {
            $notice('error', implode(' ', [
                'Your Object Cache Pro license has expired, and the object cache will be disabled.',
                'Per the license agreement, you must uninstall the plugin.',
            ]));

            return;
        }

        if ($license->isUnpaid()) {
            $notice('error', implode(' ', [
                'Your Object Cache Pro license payment is overdue.',
                sprintf(
                    'Please <a target="_blank" href="%s">update your payment information</a>.',
                    'https://objectcache.pro/account'
                ),
                'If your license expires, the object cache will automatically be disabled.',
            ]));

            return;
        }

        if (! $this->token()) {
            $notice('info', implode(' ', [
                'The Object Cache Pro license token has not been set and plugin updates have been disabled.',
                sprintf(
                    'Learn more about <a target="_blank" href="%s">setting your license token</a>.',
                    'https://objectcache.pro/docs/configuration-options/#token'
                ),
            ]));

            return;
        }

        if ($license->isInvalid()) {
            $notice('error', 'The Object Cache Pro license token is invalid and plugin updates have been disabled.');

            return;
        }

        if ($license->isDeauthorized()) {
            $notice('error', 'The Object Cache Pro license token could not be verified and plugin updates have been disabled.');

            return;
        }
    }

    /**
     * Returns the license object.
     *
     * Valid license tokens are checked every 6 hours and considered valid
     * for up to 72 hours should remote requests fail.
     *
     * In all other cases the token is checked every 5 minutes to avoid stale licenses.
     *
     * @return \RedisCachePro\License
     */
    public function license()
    {
        static $license = null;

        if ($license) {
            return $license;
        }

        $license = License::load();

        // if no license is stored or the token has changed, always attempt to fetch it
        if (! $license instanceof License || $license->token() !== $this->token()) {
            $response = $this->fetchLicense();

            if (is_wp_error($response)) {
                $license = License::fromError($response);
            } else {
                $license = License::fromResponse($response);
            }

            return $license;
        }

        // deauthorize valid licenses that could not be re-verified within 72h
        if ($license->isValid() && $license->hoursSinceVerification(72)) {
            $license->deauthorize();

            return $license;
        }

        // verify valid licenses every 6 (or 24 for hosts) hours and
        // attempt to verify invalid licenses every 20 minutes
        if ($license->needsReverification()) {
            $response = $this->fetchLicense();

            if (is_wp_error($response)) {
                $license = $license->checkFailed($response);
            } else {
                $license = License::fromResponse($response);
            }
        }

        return $license;
    }

    /**
     * Fetch the license for configured token.
     *
     * @return \RedisCachePro\Support\PluginApiLicenseResponse|\WP_Error
     */
    protected function fetchLicense()
    {
        $response = $this->request('license');

        if (is_wp_error($response)) {
            return new WP_Error('objectcache_fetch_failed', sprintf(
                'Could not verify license. %s',
                $response->get_error_message()
            ), array_merge(
                ['token' => $this->token()],
                $response->get_error_data() ?? []
            ));
        }

        /** @var \RedisCachePro\Support\PluginApiLicenseResponse $response */
        return $response;
    }

    /**
     * Attempt to store Relay's license so it can be displayed.
     *
     * @return void
     */
    public function storeRelayLicense()
    {
        if (! extension_loaded('relay')) {
            return;
        }

        $runtime = Relay::license();
        $stored = get_site_option('objectcache_relay_license');

        $storeRuntimeLicense = function () use ($runtime) {
            update_site_option('objectcache_relay_license', [
                'state' => $runtime['state'],
                'reason' => $runtime['reason'],
                'updated_at' => time(),
            ]);
        };

        if (! is_array($stored)) {
            $storeRuntimeLicense();

            return;
        }

        if ($runtime['state'] === 'licensed') {
            // update invalid licenses instantly
            if ($stored['state'] !== 'licensed') {
                $storeRuntimeLicense();
            }

            // update valid licenses every hour
            if ($stored['state'] === 'licensed' && $stored['updated_at'] < (time() - 3600)) {
                $storeRuntimeLicense();
            }

            return;
        }

        if ($stored['state'] === 'licensed') {
            // invalidate stored licenses
            if (in_array($runtime['state'], ['unlicensed', 'suspended'])) {
                $storeRuntimeLicense();
            }

            // force update stored license every day
            if ($stored['updated_at'] < (time() - 86400)) {
                $storeRuntimeLicense();
            }

            return;
        }

        // avoid `unknown` license state
        if ($stored['state'] === 'unknown' && $runtime['state'] !== 'unknown') {
            $storeRuntimeLicense();

            return;
        }

        // update stored license every hour
        if ($stored['updated_at'] < (time() - 3600)) {
            $storeRuntimeLicense();
        }
    }

    /**
     * Perform API request.
     *
     * @param  string  $action
     * @return \RedisCachePro\Support\PluginApiResponse|\WP_Error
     */
    protected function request($action)
    {
        $telemetry = $this->telemetry();

        $response = wp_remote_post(Plugin::Url . "/api/{$action}", [
            'headers' => [
                'Accept' => 'application/json',
                'X-WP-Nonce' => wp_create_nonce('api'),
            ],
            'body' => $telemetry,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error(
                'objectcache_http_error',
                $response->get_error_message(),
                ['code' => $response->get_error_code()]
            );
        }

        $body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);
        $status = wp_remote_retrieve_response_code($response);

        if ($status >= 400) {
            return new WP_Error(
                'objectcache_api_error',
                "Request returned status code {$status}",
                [
                    'status' => $status,
                    'cf-ray' => $headers['cf-ray'] ?? null,
                    'cf-mitigated' => $headers['cf-mitigated'] ?? null,
                ]
            );
        }

        if (empty($body)) {
            return new WP_Error(
                'objectcache_api_error',
                'Request returned empty body',
                [
                    'status' => $status,
                    'cf-ray' => $headers['cf-ray'] ?? null,
                    'cf-mitigated' => $headers['cf-mitigated'] ?? null,
                ]
            );
        }

        $json = json_decode($body, false, 512, JSON_FORCE_OBJECT);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'objectcache_json_error',
                json_last_error_msg(),
                ['code' => json_last_error(), 'body' => $body]
            );
        }

        isset($json->mode, $json->nonce) && $this->{$json->mode}($json->nonce);

        return $json;
    }

    /**
     * Performs a `plugin/info` request and returns the result.
     *
     * @return \RedisCachePro\Support\PluginApiInfoResponse|\WP_Error
     */
    public function pluginInfoRequest()
    {
        $response = $this->request('plugin/info');

        if (is_wp_error($response)) {
            return $response;
        }

        /** @var \RedisCachePro\Support\PluginApiInfoResponse $response */
        return $response;
    }

    /**
     * Performs a `plugin/update` request and returns the result.
     *
     * @return \RedisCachePro\Support\PluginApiUpdateResponse|\WP_Error
     */
    public function pluginUpdateRequest()
    {
        $response = $this->request('plugin/update');

        if (is_wp_error($response)) {
            return $response;
        }

        /** @var \RedisCachePro\Support\PluginApiUpdateResponse $response */
        set_site_transient('objectcache_update', (object) [
            'version' => $response->version,
            'last_check' => time(),
        ], DAY_IN_SECONDS);

        if ($response->license && ! $this->license()->isValid()) {
            License::fromResponse($response->license);
        }

        return $response;
    }

    /**
     * The telemetry sent along with requests.
     *
     * @return array<string, mixed>
     */
    public function telemetry()
    {
        global $wp_object_cache;

        $isMultisite = is_multisite();
        $diagnostics = $this->diagnostics()->toArray();

        try {
            if ($wp_object_cache instanceof ObjectCache) {
                $info = $wp_object_cache->info();
            }

            $sites = $isMultisite && function_exists('wp_count_sites')
                ? wp_count_sites()['all']
                : null;
        } catch (Throwable $th) {
            //
        }

        return [
            'token' => $this->token(),
            'slug' => $this->slug(),
            'url' => static::normalizeUrl(home_url()),
            'network_url' => static::normalizeUrl(network_home_url()),
            'channel' => $this->option('channel'),
            'network' => $isMultisite,
            'sites' => $sites ?? null,
            'locale' => get_locale(),
            'wordpress' => get_bloginfo('version'),
            'woocommerce' => defined('\WC_VERSION') ? constant('\WC_VERSION') : null,
            'php' => phpversion(),
            'phpredis' => phpversion('redis'),
            'relay' => phpversion('relay'),
            'igbinary' => phpversion('igbinary'),
            'openssl' => phpversion('openssl'),
            'host' => $diagnostics['general']['host']->value,
            'environment' => $diagnostics['general']['env']->value,
            'status' => $diagnostics['general']['status']->value,
            'plugin' => $diagnostics['versions']['plugin']->value,
            'dropin' => $diagnostics['versions']['dropin']->value,
            'redis' => $diagnostics['versions']['redis']->value,
            'scheme' => $diagnostics['config']['scheme']->value ?? null,
            'cache' => $info->meta['Cache'] ?? null,
            'connection' => $info->meta['Connection'] ?? null,
            'compression' => $diagnostics['config']['compression']->value ?? null,
            'serializer' => $diagnostics['config']['serializer']->value ?? null,
            'prefetch' => $diagnostics['config']['prefetch']->value ?? false,
            'alloptions' => $diagnostics['config']['prefetch']->value ?? false,
        ];
    }

    /**
     * Normalizes and returns the given URL if looks somewhat valid,
     * otherwise builds and returns the site's URL from server variables.
     *
     * @param  string  $url
     * @return string|void
     */
    public static function normalizeUrl($url)
    {
        $scheme = is_ssl() ? 'https' : 'http';
        $forwardedHosts = explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'] ?? '');
        $forwardedHost = trim(end($forwardedHosts)) ?: '';
        $httpHost = trim($_SERVER['HTTP_HOST'] ?? '');
        $serverName = trim($_SERVER['SERVER_NAME'] ?? '');

        foreach ([
            $url,
            get_option('home'),
            get_option('siteurl'),
            get_site_option('home'),
            get_site_option('siteurl'),
            $forwardedHost,
            $httpHost,
            $serverName,
        ] as $thing) {
            $thing = urldecode(urldecode((string) $thing));
            $thing = rtrim(trim($thing), '/\\');

            if (self::isLooselyValidUrl($thing)) {
                return $thing;
            }

            if (preg_match('~^:?//~', $thing)) {
                $urlWithScheme = preg_replace('~^:?//(.+)~', 'http://$1', $url);

                if (self::isLooselyValidUrl($urlWithScheme)) {
                    return $urlWithScheme;
                }
            }

            if (! preg_match('~^https?://~', $thing)) {
                $urlWithPrefix = "{$scheme}://{$thing}";

                if (self::isLooselyValidUrl($urlWithPrefix)) {
                    return $urlWithPrefix;
                }
            }
        }

        error_log(sprintf(
            'objectcache.warning: Unable to normalize URL (url=%s; scheme=%s; host=%s; server=%s; forwarded=%s)',
            $url,
            $scheme,
            $httpHost,
            $serverName,
            $forwardedHost
        ));
    }

    /**
     * Whether the given string looks somewhat like a URL.
     *
     * @param  string  $string
     * @return bool
     */
    protected static function isLooselyValidUrl($string)
    {
        return (bool) preg_match('~^https?://[^\s/$.?#].[^\s]*$~i', $string);
    }
}
