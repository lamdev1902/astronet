<?php

defined('ABSPATH') || exit;

spl_autoload_register(function ($fqcn) {
    if (strpos($fqcn, 'RedisCachePro\\') === 0) {
        require_once str_replace(['\\', 'RedisCachePro/'], ['/', __DIR__ . '/src/'], $fqcn) . '.php';
    }
});

(function ($config) {
    if (defined('WP_REDIS_CONFIG') || empty($config)) {
        return;
    }

    $config = json_decode((string) $config, true);
    $error = json_last_error();

    if ($error !== JSON_ERROR_NONE || ! is_array($config)) {
        error_log(sprintf(
            'objectcache.warning: Unable to decode `OBJECTCACHE_CONFIG` environment variable (%s)',
            json_last_error_msg()
        ));

        return;
    }

    $array_replace_recursive = function ($current, $override) use (&$array_replace_recursive) {
        foreach ($override as $key => $value) {
            if (array_key_exists($key, $current) && is_array($current[$key]) && $current[$key] !== array_values($current[$key])) {
                $current[$key] = $array_replace_recursive($current[$key], $value);
            } else {
                $current[$key] = $value;
            }
        }

        return $current;
    };

    $array_merge_recursive = function ($current, $merge) use (&$array_merge_recursive) {
        foreach ($merge as $key => $value) {
            if (! array_key_exists($key, $current) || ! is_array($current[$key])) {
                $current[$key] = $value;
            } elseif ($current[$key] === array_values($current[$key])) {
                $current[$key] = array_merge($current[$key], (array) $value);
            } else {
                $current[$key] = $array_merge_recursive($current[$key], $value);
            }
        }

        return $current;
    };

    if (defined('OBJECTCACHE_OVERRIDE')) {
        $config = $array_replace_recursive($config, OBJECTCACHE_OVERRIDE);
    } elseif (defined('OBJECTCACHE_MERGE')) {
        $config = $array_merge_recursive($config, OBJECTCACHE_MERGE);
    }

    define('WP_REDIS_CONFIG', $config);
})(getenv('OBJECTCACHE_CONFIG'));
