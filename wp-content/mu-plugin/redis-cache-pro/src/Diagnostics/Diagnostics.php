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

namespace RedisCachePro\Diagnostics;

use Redis;
use WP_Error;
use ArrayAccess;
use LogicException;

use Relay\Relay;

use RedisCachePro\License;
use RedisCachePro\ObjectCaches\ObjectCache;
use RedisCachePro\Connections\RelayConnection;
use RedisCachePro\Configuration\Configuration;
use RedisCachePro\Connectors\RelayConnector;
use RedisCachePro\Connectors\PhpRedisConnector;

use const RedisCachePro\Basename;

/**
 * @implements \ArrayAccess<string, array<string, mixed>>
 */
class Diagnostics implements ArrayAccess
{
    /**
     * Group: Configuration values.
     *
     * @var string
     */
    const CONFIG = 'config';

    /**
     * Group: Constants.
     *
     * @var string
     */
    const CONSTANTS = 'constants';

    /**
     * Group: Environment Variables.
     *
     * @var string
     */
    const ENV = 'environment';

    /**
     * Group: Errors.
     *
     * @var string
     */
    const ERRORS = 'errors';

    /**
     * Group: General information.
     *
     * @var string
     */
    const GENERAL = 'general';

    /**
     * Group: Cache group information.
     *
     * @var string
     */
    const GROUPS = 'groups';

    /**
     * Group: Relay information.
     *
     * @var string
     */
    const RELAY = 'relay';

    /**
     * Group: Version numbers.
     *
     * @var string
     */
    const VERSIONS = 'versions';

    /**
     * Group: Statistics.
     *
     * @var string
     */
    const STATISTICS = 'statistics';

    /**
     * The object cache instance.
     *
     * @var \RedisCachePro\ObjectCaches\ObjectCache|null
     */
    protected $cache;

    /**
     * The configuration instance.
     *
     * @var \RedisCachePro\Configuration\Configuration|null
     */
    protected $config;

    /**
     * The connection's client instance.
     *
     * @var \RedisCachePro\Clients\ClientInterface|null
     */
    protected $client;

    /**
     * The connection instance.
     *
     * @var \RedisCachePro\Connections\ConnectionInterface|null
     */
    protected $connection;

    /**
     * Holds the diagnostics groups and their data.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Create a new diagnostics instance.
     *
     * @param  mixed  $cache
     */
    public function __construct($cache)
    {
        if ($cache instanceof ObjectCache) {
            $this->cache = $cache;
            $this->client = $this->cache->client();
            $this->config = $this->cache->config();
            $this->connection = $this->cache->connection();
        }

        if (! $this->config && defined('\WP_REDIS_CONFIG')) {
            $this->config = Configuration::safelyFrom(\WP_REDIS_CONFIG);
        }

        $this->gatherGeneral();
        $this->gatherRelay();
        $this->gatherVersions();
        $this->gatherStatistics();
        $this->gatherGroups();
        $this->gatherConfiguration();
        $this->gatherEnvironmentVariables();
        $this->gatherConstants();
        $this->gatherErrors();
    }

    /**
     * Gathers general information, such as the used object cache class,
     * the connection status and eviction policy.
     *
     * @return void
     */
    protected function gatherGeneral()
    {
        $this->data[self::GENERAL]['status'] = $this->status();
        $this->data[self::GENERAL]['dropin'] = $this->dropin();
        $this->data[self::GENERAL]['license'] = $this->license();

        $this->data[self::GENERAL]['env'] = Diagnostic::name('Environment')->value(self::environment());

        if ($this->cache) {
            $this->data[self::GENERAL]['multisite'] = Diagnostic::name('Multisite')->value($this->cache->isMultisite() ? 'Yes' : 'No');
        }

        if ($this->isDisabledUsingEnvVar()) {
            $this->data[self::GENERAL]['disabled'] = Diagnostic::name('Disabled')
                ->error('Using WP_REDIS_DISABLED environment variable');
        }

        if ($this->isDisabledUsingConstant()) {
            $this->data[self::GENERAL]['disabled'] = Diagnostic::name('Disabled')
                ->error('Using WP_REDIS_DISABLED constant');
        }

        $this->data[self::GENERAL]['mu'] = Diagnostic::name('Must-use')->value(self::isMustUse() ? 'Yes' : 'No');
        $this->data[self::GENERAL]['vcs'] = Diagnostic::name('VCS')->value(self::usingVCS() ? 'Yes' : 'No');
        $this->data[self::GENERAL]['host'] = Diagnostic::name('Hosting')->value(self::host());
        $this->data[self::GENERAL]['eviction-policy'] = $this->evictionPolicy();
        $this->data[self::GENERAL]['compressions'] = $this->compressions();
        $this->data[self::GENERAL]['basename'] = Diagnostic::name('Basename')->value(Basename);

        if ($this->client) {
            $this->data[self::GENERAL]['client'] = Diagnostic::name('Client')->value(get_class($this->client));
        }
    }

    /**
     * ...
     *
     * @return void
     */
    protected function gatherRelay()
    {
        if (! extension_loaded('relay')) {
            return;
        }

        if ($this->usingRelay()) {
            $this->gatherRelayStatistics();
        }

        $this->data[self::RELAY]['relay-cache'] = $this->relayCache();
        $this->data[self::RELAY]['relay-eviction'] = $this->relayEvictionPolicy();
        $this->data[self::RELAY]['relay-license'] = $this->relayLicense();

        $percentage = (int) ini_get('relay.maxmemory_pct') ?: 100;

        $this->data[self::RELAY]['relay-maxmemory'] = Diagnostic::name('Relay MaxMemory')
            ->value("{$percentage}%");
    }

    /**
     * Gathers version numbers for PHP, extensions, Redis and the drop-in.
     *
     * @return void
     */
    protected function gatherVersions()
    {
        $this->data[self::VERSIONS]['php'] = $this->phpVersion();
        $this->data[self::VERSIONS]['igbinary'] = $this->igbinary();
        $this->data[self::VERSIONS]['phpredis'] = $this->phpredis();
        $this->data[self::VERSIONS]['relay'] = $this->relay();
        $this->data[self::VERSIONS]['redis'] = $this->redisVersion();
        $this->data[self::VERSIONS]['plugin'] = $this->pluginVersion();
        $this->data[self::VERSIONS]['dropin'] = $this->dropinVersion();
    }

    /**
     * Gathers memory and usage statistics.
     *
     * @return void
     */
    protected function gatherStatistics()
    {
        $this->gatherRedisStatistics();
    }

    /**
     * Gathers cache group information.
     *
     * @return void
     */
    protected function gatherGroups()
    {
        $this->data[self::GROUPS]['global'] = Diagnostic::name('Global')
            ->prettyJson($this->cache ? $this->cache->globalGroups() : null);

        $this->data[self::GROUPS]['non-persistent'] = Diagnostic::name('Non-persistent')
            ->prettyJson($this->cache ? $this->cache->nonPersistentGroups() : null);

        $this->data[self::GROUPS]['non-prefetchable'] = Diagnostic::name('Non-prefetchable')
            ->prettyJson($this->cache ? $this->cache->nonPrefetchableGroups() : null);
    }

    /**
     * Gathers Redis related statistics.
     *
     * @return void
     */
    protected function gatherRedisStatistics()
    {
        $memory = Diagnostic::name('Redis Memory');
        $usedMemory = $this->usedMemory();

        if ($usedMemory) {
            $maxMemory = $this->maxMemory();

            $memory->value(
                $maxMemory
                    ? sprintf('%s of %s', size_format($usedMemory), size_format($maxMemory))
                    : size_format($usedMemory, 2)
            );
        }

        $this->data[self::STATISTICS]['redis-memory'] = $memory;

        $keys = Diagnostic::name('Redis Keys');

        if ($this->connection && ! $this->config->cluster) {
            $keys->value($this->connection->memoize('dbsize'));
        }

        $this->data[self::STATISTICS]['redis-keys'] = $keys;
    }

    /**
     * Gathers Relay related statistics.
     *
     * @return void
     */
    protected function gatherRelayStatistics()
    {
        /** @var \RedisCachePro\Connections\RelayConnection $connection */
        $connection = $this->connection;

        $stats = $connection->memoize('stats');
        $endpointId = $connection->endpointId();

        $humanMemory = sprintf(
            '%s of %s',
            size_format($stats['memory']['used']),
            size_format($stats['memory']['total'])
        );

        $relayMemory = Diagnostic::name('Relay Memory');

        if ($this->relayAtCapacity()) {
            $relayMemory->error($humanMemory);
        } else {
            $relayMemory->value($humanMemory);
        }

        $this->data[self::STATISTICS]['relay-memory'] = $relayMemory;

        $keys = array_sum(array_map(function ($connection) {
            return $connection['keys'][$this->config->database] ?? 0;
        }, $stats['endpoints'][$endpointId]['connections'] ?? [])) ?: null;

        $this->data[self::STATISTICS]['relay-keys'] = Diagnostic::name('Relay Keys')
            ->value($keys);
    }

    /**
     * Gathers configuration values from the config instance.
     *
     * @return void
     */
    protected function gatherConfiguration()
    {
        if ($this->config) {
            foreach ($this->config->diagnostics() as $option => $value) {
                $this->data[self::CONFIG][$option] = Diagnostic::name($option)->value($value);
            }
        }
    }

    /**
     * Gathers relevant constants.
     *
     * @return void
     */
    protected function gatherConstants()
    {
        $constants = [
            'WP_DEBUG',
            'SAVEQUERIES',
            'WP_REDIS_DIR',
            'WP_REDIS_DISABLED',
            'WP_REDIS_CONFIG',
        ];

        foreach ($constants as $constant) {
            $diagnostic = Diagnostic::name($constant);

            if (defined($constant)) {
                $value = constant($constant);

                if (is_string($value)) {
                    $diagnostic->value($value);
                } elseif (is_array($value)) {
                    $diagnostic->prettyJson($value);
                } else {
                    $diagnostic->json($value);
                }
            } else {
                $diagnostic->value('undefined');
            }

            $this->data[self::CONSTANTS][$constant] = $diagnostic;
        }
    }

    /**
     * Gathers relevant environment variables.
     *
     * @return void
     */
    protected function gatherEnvironmentVariables()
    {
        $variables = [
            'WP_REDIS_DISABLED',
            'OBJECTCACHE_CONFIG',
        ];

        foreach ($variables as $variable) {
            $diagnostic = Diagnostic::name($variable);
            $value = getenv($variable);

            if ($value !== false) {
                if (strpos($value, '{') === 0) {
                    $diagnostic->prettyJson(json_decode($value, true, 3));
                } else {
                    $diagnostic->value($value);
                }
            } else {
                $diagnostic->value('undefined');
            }

            $this->data[self::ENV][$variable] = $diagnostic;
        }
    }

    /**
     * Gathers all occurred errors.
     *
     * @return void
     */
    protected function gatherErrors()
    {
        global $wp_object_cache_errors;

        if ($this->config->initException ?? false) {
            $this->data[self::ERRORS][] = sprintf(
                'The configuration could not be instantiated: %s',
                $this->config->initException->getMessage()
            );
        }

        if (empty($wp_object_cache_errors)) {
            return;
        }

        foreach ($wp_object_cache_errors as $error) {
            $this->data[self::ERRORS][] = $error;
        }
    }

    /**
     * Append filesystem access diagnostic to general group.
     *
     * @return self
     */
    public function withFilesystemAccess()
    {
        $fs = $this->filesystemAccess();

        $diagnostic = Diagnostic::name('Filesystem');

        if (is_wp_error($fs)) {
            $diagnostic->error($fs->get_error_message());
        } else {
            $diagnostic->value('Accessible');
        }

        $this->data[self::GENERAL]['filesystem'] = $diagnostic;

        return $this;
    }

    /**
     * Return the license status.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function license()
    {
        $diagnostic = Diagnostic::name('License');

        if (! $this->config || ! $this->config->token) {
            return $diagnostic->error('Missing token');
        }

        $license = License::load();

        if (! $license instanceof License) {
            return $diagnostic;
        }

        if ($license->isValid()) {
            return $diagnostic->value(ucwords((string) $license->state()));
        }

        return $diagnostic->error(ucwords((string) $license->state()));
    }

    /**
     * Return Relay license status.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function relayLicense()
    {
        $license = get_site_option('objectcache_relay_license');

        if (! is_array($license)) {
            $license = Relay::license();
        }

        $diagnostic = Diagnostic::name('Relay License');

        if (! empty($license['reason'])) {
            $diagnostic->comment($license['reason']);
        }

        $state = (string) $license['state'];

        switch ($state) {
            case 'licensed':
                return $diagnostic->success(ucwords($state));
            case 'unknown':
                return $diagnostic->value(ucwords($state));
            case 'suspended':
                return $diagnostic->error(ucwords($state));
        }

        // Handle `unlicensed` state
        switch ($license['reason']) {
            case 'NO_KEY':
                return $diagnostic->value(ucwords($state));
            case 'UNKNOWN_KEY':
            case 'DEFAULT_UUID':
            case 'EARLY_HEARTBEAT':
            case 'BINARY_SUSPENDED':
            case 'LICENSE_SUSPENDED':
            case 'INVALID_HEARTBEAT':
            case 'SUBSCRIPTION_INACTIVE':
                return $diagnostic->error(ucwords($state));
        }

        return $diagnostic->error(ucwords($state));
    }

    /**
     * Returns Relay's cache mode.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function relayCache()
    {
        $diagnostic = Diagnostic::name('Relay Cache');

        return $this->usingRelayCache()
            ? $diagnostic->value('Enabled')
            : $diagnostic->value('Disabled')->comment('client only');
    }

    /**
     * Returns Relay's eviction policy, if a connection is established.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function relayEvictionPolicy()
    {
        $policy = ini_get('relay.eviction_policy');
        $diagnostic = Diagnostic::name('Relay Eviction');

        if ($policy === 'noeviction') {
            return $diagnostic->error($policy);
        }

        return $diagnostic->value($policy);
    }

    /**
     * Returns Relay's max-memory percentage.
     *
     * @return int
     */
    public function relayMemoryThreshold()
    {
        return (int) ini_get('relay.maxmemory_pct') ?: 100;
    }

    /**
     * Return the object cache drop-in status.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function dropin()
    {
        $diagnostic = Diagnostic::name('Drop-in');

        if (! $this->dropinExists()) {
            return $diagnostic->error('Not enabled');
        }

        if (! $this->dropinIsValid()) {
            return $diagnostic->error('Invalid');
        }

        if (! $this->dropinIsUpToDate()) {
            return $diagnostic->warning('Outdated');
        }

        return $diagnostic->value('Valid');
    }

    /**
     * Returns the drop-in version, if present.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function dropinVersion()
    {
        $diagnostic = Diagnostic::name('Drop-in');

        if (! $this->dropinExists()) {
            return $diagnostic;
        }

        $dropin = $this->fileMetadata(WP_CONTENT_DIR . '/object-cache.php');
        $stub = $this->fileMetadata(__DIR__ . '/../../stubs/object-cache.php');

        if ($dropin['Version'] !== $stub['Version']) {
            return $diagnostic->error($dropin['Version'])->comment('Outdated');
        }

        return $diagnostic->value($dropin['Version']);
    }

    /**
     * Returns Redis' eviction policy, if a connection is established.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function evictionPolicy()
    {
        $policy = $this->maxMemoryPolicy();
        $diagnostic = Diagnostic::name('Eviction Policy');

        if ($policy === 'noeviction' && ! $this->config->maxttl) {
            return $diagnostic->error($policy);
        }

        return $diagnostic->value($policy);
    }

    /**
     * Returns the used memory, if a connection is established.
     *
     * @return string|null
     */
    public function usedMemory()
    {
        return $this->redisInfoKey('used_memory');
    }

    /**
     * Returns the max memory, if a connection is established.
     *
     * @return string|null
     */
    public function maxMemory()
    {
        return $this->redisInfoKey('maxmemory');
    }

    /**
     * Return the `maxmemory_policy` from Redis.
     *
     * @return string|null
     */
    public function maxMemoryPolicy()
    {
        return $this->redisInfoKey('maxmemory_policy');
    }

    /**
     * Returns details about the igbinary extension.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function igbinary()
    {
        $diagnostic = Diagnostic::name('igbinary');

        if (! extension_loaded('igbinary')) {
            return $diagnostic->error('Not installed');
        }

        $version = phpversion('igbinary');

        if (! defined('Redis::SERIALIZER_IGBINARY')) {
            return $diagnostic->value($version)->comment('No PhpRedis support');
        }

        return $diagnostic->value($version);
    }

    /**
     * Returns details about the PhpRedis extension.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function phpredis()
    {
        $diagnostic = Diagnostic::name('PhpRedis');

        if (! extension_loaded('redis')) {
            return $diagnostic->error('Not installed');
        }

        $version = (string) phpversion('redis');

        if (version_compare($version, PhpRedisConnector::RequiredVersion, '<')) {
            return $diagnostic->error($version)->comment('Unsupported');
        }

        if (version_compare($version, '5.3.7', '<')) {
            return $diagnostic->warning($version)->comment('Outdated');
        }

        return $diagnostic->value($version);
    }

    /**
     * Returns details about the Relay extension.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function relay()
    {
        $diagnostic = Diagnostic::name('Relay');

        if (! extension_loaded('relay')) {
            return $diagnostic->error('Not installed');
        }

        $version = (string) phpversion('relay');

        if (version_compare($version, RelayConnector::RequiredVersion, '<')) {
            return $diagnostic->error($version)->comment('Unsupported');
        }

        if (version_compare($version, '0.6.1', '<')) {
            return $diagnostic->warning($version)->comment('Outdated');
        }

        return $diagnostic->value($version);
    }

    /**
     * Returns details about the PHP version.
     *
     * Outdated comment is based on:
     * https://www.php.net/supported-versions.php
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function phpVersion()
    {
        $diagnostic = Diagnostic::name('PHP');

        $version = phpversion();

        // 7.4 security support until 26 Nov 2023
        if (version_compare($version, '8.0', '<')) {
            return $diagnostic->warning($version)->comment('Outdated');
        }

        // 8.0 (security support until 26 Nov 2023)
        if (version_compare($version, '8.1', '<') && date('Y') >= 2024) {
            return $diagnostic->warning($version)->comment('Outdated');
        }

        // 8.1 (security support until 25 Nov 2024)
        if (version_compare($version, '8.2', '<') && date('Y') >= 2025) {
            return $diagnostic->warning($version)->comment('Outdated');
        }

        return $diagnostic->value($version);
    }

    /**
     * Returns whether Redis responds to a PING command.
     *
     * @return bool
     */
    public function ping()
    {
        if ($this->connection) {
            return (bool) $this->connection->memoize('ping');
        }

        return false;
    }

    /**
     * Returns the status of the Redis connection.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function status()
    {
        $diagnostic = Diagnostic::name('Status')->labels([
            -1 => 'Disabled',
            0 => 'Not connected',
            1 => 'Connected',
        ]);

        if ($this->isDisabled()) {
            return $diagnostic->warning(-1);
        }

        return $this->ping()
            ? $diagnostic->success(1)
            : $diagnostic->error(0);
    }

    /**
     * Returns a list of supported compression algorithms.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function compressions()
    {
        $client = $this->usingRelay()
            ? Relay::class
            : Redis::class;

        $algorithms = array_filter([
            defined("{$client}::COMPRESSION_LZF") ? 'LZF' : null,
            defined("{$client}::COMPRESSION_LZ4") ? 'LZ4' : null,
            defined("{$client}::COMPRESSION_ZSTD") ? 'ZSTD' : null,
        ]);

        $diagnostic = Diagnostic::name('Compressions');

        return empty($algorithms)
            ? $diagnostic->value('None')
            : $diagnostic->values($algorithms);
    }

    /**
     * Returns the plugin version, if installed.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    protected function pluginVersion()
    {
        $plugin = $this->pluginMetadata();

        return Diagnostic::name('Plugin')->value(
            $plugin['Version'] ?? null
        );
    }

    /**
     * Returns the Redis server version, if a connection is established.
     *
     * @return \RedisCachePro\Diagnostics\Diagnostic
     */
    public function redisVersion()
    {
        $diagnostic = Diagnostic::name('Redis');

        $version = $this->redisInfoKey('redis_version');

        if (! $version) {
            return $diagnostic->value($version);
        }

        if (version_compare($version, '4.0', '<')) {
            return $diagnostic->warning($version)->comment('Outdated');
        }

        if ($this->usingRelayCache() && version_compare($version, '6.2.7', '<')) {
            return $diagnostic->error($version)->comment('Unsupported');
        }

        return $diagnostic->value($version);
    }

    /**
     * Returns information and statistics about the Redis server.
     *
     * @return array<mixed>|null
     */
    protected function redisInfo()
    {
        $info = null;

        if ($this->connection) {
            $info = $this->connection->memoize('info');
        }

        return $info;
    }

    /**
     * Returns specific key from Redis `INFO` call.
     *
     * @param  string  $name
     * @return string|null
     */
    protected function redisInfoKey($name)
    {
        $info = $this->redisInfo();

        return $info[$name] ?? null;
    }

    /**
     * Whether the object cache is powered by Relay.
     *
     * @return bool
     */
    public function usingRelay()
    {
        if ($this->connection) {
            return $this->connection instanceof RelayConnection;
        }

        return false;
    }

    /**
     * Whether the object cache is using Relay's in-memory cache.
     *
     * @return bool
     */
    public function usingRelayCache()
    {
        return $this->usingRelay()
            && $this->relayHasCache();
    }

    /**
     * Whether Relay as memory allocated to it, or is client-only.
     *
     * @return bool
     */
    public function relayHasCache()
    {
        $enabled = $this->config->relay->cache;

        if (method_exists(Relay::class, 'maxMemory')) { // @phpstan-ignore-line
            return $enabled && Relay::maxMemory() > 0;
        }

        if (method_exists(Relay::class, 'memory')) { // @phpstan-ignore-line
            return $enabled && Relay::memory() > 0;
        }

        return true;
    }

    /**
     * Whether Relay is running at maximum memory capacity.
     *
     * @return bool
     */
    public function relayAtCapacity()
    {
        if (! $this->connection instanceof RelayConnection) {
            return false;
        }

        $stats = $this->connection->memoize('stats');
        $percentage = $this->relayMemoryThreshold();
        $margin = 2;

        $threshold = (($percentage - $margin) / 100) * $stats['memory']['total'];

        return $stats['memory']['used'] > $threshold;
    }

    /**
     * Whether the object cache is disabled.
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->isDisabledUsingEnvVar()
            || $this->isDisabledUsingConstant();
    }

    /**
     * Whether the object cache is disabled using the `WP_REDIS_DISABLED` constant.
     *
     * @return bool
     */
    public function isDisabledUsingConstant()
    {
        return defined('WP_REDIS_DISABLED') && WP_REDIS_DISABLED;
    }

    /**
     * Whether the object cache is disabled using the `WP_REDIS_DISABLED` environment variable.
     *
     * @return bool
     */
    public function isDisabledUsingEnvVar()
    {
        return ! empty(getenv('WP_REDIS_DISABLED'));
    }

    /**
     * Whether the plugin is installed as a must-use plugin.
     *
     * @return bool
     */
    public static function isMustUse()
    {
        static $mustuse;

        if (! $mustuse) {
            $mustuse = Basename === 'mu-plugin.php'
                ?: array_key_exists(Basename, get_mu_plugins());
        }

        return $mustuse;
    }

    /**
     * Whether the plugin is using version control.
     *
     * @return bool
     */
    public static function usingVCS()
    {
        static $vcs;

        if (! $vcs) {
            $vcs = @is_dir(realpath(__DIR__ . '/../..') . '/.git');
        }

        return $vcs;
    }

    /**
     * Whether the object cache drop-in file exists.
     *
     * @return bool
     */
    public function dropinExists()
    {
        return file_exists(WP_CONTENT_DIR . '/object-cache.php');
    }

    /**
     * Whether the object cache drop-in is valid.
     *
     * @return bool
     */
    public function dropinIsValid()
    {
        $plugin = $this->pluginMetadata();
        $dropin = $this->dropinMetadata();

        $isValid = $dropin['PluginURI'] === $plugin['PluginURI'];

        /**
         * Filter the drop-in validation result.
         *
         * @param  bool  $is_valid  Whether the drop-in is valid.
         * @param  array  $dropin  The drop-in metadata.
         * @param  array  $plugin  The plugin metadata.
         */
        return (bool) apply_filters(
            'objectcache_validate_dropin',
            $isValid,
            $dropin,
            $plugin
        );
    }

    /**
     * Whether the object cache drop-in is up-to-date.
     *
     * @return bool
     */
    public function dropinIsUpToDate()
    {
        $plugin = $this->pluginMetadata();
        $dropin = $this->dropinMetadata();

        $upToDate = version_compare($dropin['Version'], $plugin['Version'], '>=');

        /**
         * Filter the drop-in version check result.
         *
         * @param  bool  $is_uptodate  Whether the drop-in is up-to-date.
         * @param  array  $dropin  The drop-in metadata.
         * @param  array  $plugin  The plugin metadata.
         */
        return (bool) apply_filters(
            'objectcache_validate_dropin_version',
            $upToDate,
            $dropin,
            $plugin
        );
    }

    /**
     * Test whether the filesystem access can be obtained and is working.
     *
     * @return WP_Error|true
     */
    public function filesystemAccess()
    {
        global $wp_filesystem;

        if (! WP_Filesystem()) {
            return new WP_Error('fs', 'Failed to obtain filesystem write access.');
        }

        $stub = realpath(__DIR__ . '/../../stubs/object-cache.php');
        $temp = WP_CONTENT_DIR . '/object-cache.tmp';

        if (! $wp_filesystem->exists($stub)) {
            return new WP_Error('fs', 'Stub file does not exist');
        }

        if (! $wp_filesystem->is_writable(WP_CONTENT_DIR)) {
            return new WP_Error('fs', 'Unable to write to content directory');
        }

        if ($wp_filesystem->exists($temp)) {
            if (! $wp_filesystem->delete($temp)) {
                return new WP_Error('fs', 'Unable to delete existing test file');
            }
        }

        if (! $wp_filesystem->copy($stub, $temp, true, FS_CHMOD_FILE)) {
            return new WP_Error('fs', 'Failed to copy test file');
        }

        if (! $wp_filesystem->exists($temp)) {
            return new WP_Error('fs', 'Unable to verify existence of copied test file');
        }

        if (! $wp_filesystem->is_readable($temp)) {
            return new WP_Error('fs', 'Unable to read copied test file');
        }

        if ($wp_filesystem->size($stub) !== $wp_filesystem->size($temp)) {
            return new WP_Error('fs', 'Size of copied test file does not match');
        }

        if (! $wp_filesystem->delete($temp)) {
            return new WP_Error('fs', 'Unable to delete copied test file');
        }

        return true;
    }

    /**
     * May return the environment type.
     *
     * @return string|void
     */
    public static function environment()
    {
        if (function_exists('wp_get_environment_type')) {
            return wp_get_environment_type();
        }

        if (defined('WP_ENV')) {
            return WP_ENV;
        }
    }

    /**
     * May return the name of the hosting provider.
     *
     * @return string|void
     */
    public static function host()
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';

        if (
            strpos($documentRoot, '.cloudwaysapps.com/') ||
            strpos($documentRoot, '.cloudwaysstagingapps.com/') ||
            isset($_SERVER['cw_allowed_ip']) ||
            isset($_SERVER['CW_ALLOWED_IP'])
        ) {
            return 'cloudways';
        }

        if (
            (defined('PAGELYBIN') && constant('PAGELYBIN')) ||
            strpos($_SERVER['PHP_INI_SCAN_DIR'] ?? '', 'srv/pagely/conf') ||
            preg_match('~^/data/s\d+/dom\d+~', $documentRoot)
        ) {
            return 'pagely';
        }

        if (
            ! empty($_SERVER['WPAAS_SITE_ID']) ||
            (class_exists('\WPaaS\Plugin') && \WPaaS\Plugin::is_wpaas())
        ) {
            return 'godaddy';
        }

        if (defined('CONVESIO_VER') || strpos($documentRoot, 'convesio')) {
            return 'convesio';
        }

        if (defined('NEXCESS_MAPPS_SITE') || defined('NEXCESS_MAPPS_TOKEN')) {
            return 'nexcess';
        }

        if (strpos((string) gethostname(), '.onrocket.com')) {
            return 'rocket';
        }

        if (defined('GRIDPANE') && constant('GRIDPANE')) {
            return 'gridpane';
        }

        if (getenv('SPINUPWP_CACHE_PATH')) {
            return 'spinupwp';
        }

        if (isset($_ENV['PANTHEON_ENVIRONMENT']) || defined('PANTHEON_ENVIRONMENT')) {
            return 'pantheon';
        }

        if (! empty($_SERVER['CS_AUTH_KEY'])) {
            return 'cloudpress';
        }

        if (defined('IS_PRESSABLE') && constant('IS_PRESSABLE')) {
            return 'pressable';
        }

        if (defined('KINSTAMU_VERSION')) {
            return 'kinsta';
        }

        if (defined('FLYWHEEL_PLUGIN_DIR')) {
            return 'flywheel';
        }

        if (class_exists('WpeCommon') || getenv('IS_WPE')) {
            return 'wpengine';
        }

        if (defined('WPCOMSH_VERSION') && constant('WPCOMSH_VERSION')) {
            return 'wpcom';
        }

        if (isset($_SERVER['DH_USER'])) {
            return 'dreampress';
        }
    }

    /**
     * Returns the given file's header metadata.
     *
     * @param  string  $path
     * @return array<string, string>
     */
    protected function fileMetadata($path)
    {
        static $cache = [];

        $path = realpath((string) $path);

        if ($path && ! isset($cache[$path])) {
            $cache[$path] = \get_plugin_data($path, false, false);
        }

        return $cache[$path] ?? [
            'Name' => '',
            'PluginURI' => '',
            'Version' => '',
            'Author' => '',
            'AuthorURI' => '',
            'AuthorName' => '',
        ];
    }

    /**
     * Parses the plugin file's metadata once.
     *
     * @return array<string, string>
     */
    public function pluginMetadata()
    {
        $file = realpath(__DIR__ . '/../../object-cache-pro.php')
            ?: realpath(__DIR__ . '/../../redis-cache-pro.php');

        return $this->fileMetadata((string) $file);
    }

    /**
     * Parses the drop-in file's metadata once.
     *
     * @return array<string, string>
     */
    public function dropinMetadata()
    {
        return $this->fileMetadata(WP_CONTENT_DIR . '/object-cache.php');
    }

    /**
     * Returns the diagnostic information.
     *
     * @return array<string, array<string, mixed>>
     */
    public function toArray()
    {
        return [
            self::GENERAL => $this->data[self::GENERAL] ?? null,
            self::ERRORS => $this->data[self::ERRORS] ?? null,
            self::RELAY => $this->data[self::RELAY] ?? null,
            self::VERSIONS => $this->data[self::VERSIONS] ?? null,
            self::STATISTICS => $this->data[self::STATISTICS] ?? null,
            self::GROUPS => $this->data[self::GROUPS] ?? null,
            self::CONFIG => $this->data[self::CONFIG] ?? null,
            self::ENV => $this->data[self::ENV] ?? null,
            self::CONSTANTS => $this->data[self::CONSTANTS] ?? null,
        ];
    }

    /**
     * Interface method to provide accessing diagnostics as array.
     *
     * @param  string  $group
     * @return bool
     */
    public function offsetExists($group): bool
    {
        return isset($this->data[$group]);
    }

    /**
     * Interface method to provide accessing diagnostics as array.
     *
     * @param  string  $group
     * @return array<string, mixed>|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($group)
    {
        return $this->data[$group] ?: null;
    }

    /**
     * Interface method to provide accessing diagnostics as array.
     *
     * @param  string  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void // phpcs:ignore PHPCompatibility
    {
        throw new LogicException('Diagnostics cannot be set');
    }

    /**
     * Interface method to provide accessing diagnostics as array.
     *
     * @param  string  $offset
     * @return void
     */
    public function offsetUnset($offset): void // phpcs:ignore PHPCompatibility
    {
        throw new LogicException('Diagnostics cannot be unset');
    }
}
