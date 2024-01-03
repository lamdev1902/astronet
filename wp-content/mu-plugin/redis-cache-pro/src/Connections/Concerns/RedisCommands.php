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

namespace RedisCachePro\Connections\Concerns;

trait RedisCommands
{
    /**
     * List of read-only Redis commands.
     *
     * ```
     * curl --silent "https://raw.githubusercontent.com/redis/redis-doc/master/commands.json" \
     *   | jq -r 'with_entries( select( .value.command_flags[]? | contains("readonly") ) ) | keys'
     * ```
     *
     * @var array<int, string>
     */
    protected $readonly = [
        'BITCOUNT',
        'BITFIELD_RO',
        'BITPOS',
        'DBSIZE',
        'DUMP',
        'EVALSHA_RO',
        'EVAL_RO',
        'EXISTS',
        'EXPIRETIME',
        'FCALL_RO',
        'GEODIST',
        'GEOHASH',
        'GEOPOS',
        'GEORADIUSBYMEMBER_RO',
        'GEORADIUS_RO',
        'GEOSEARCH',
        'GET',
        'GETBIT',
        'GETRANGE',
        'HEXISTS',
        'HGET',
        'HGETALL',
        'HKEYS',
        'HLEN',
        'HMGET',
        'HRANDFIELD',
        'HSCAN',
        'HSTRLEN',
        'HVALS',
        'KEYS',
        'LCS',
        'LINDEX',
        'LLEN',
        'LOLWUT',
        'LPOS',
        'LRANGE',
        'MEMORY USAGE',
        'MGET',
        'OBJECT',
        'PEXPIRETIME',
        'PFCOUNT',
        'PTTL',
        'RANDOMKEY',
        'SCAN',
        'SCARD',
        'SDIFF',
        'SINTER',
        'SINTERCARD',
        'SISMEMBER',
        'SMEMBERS',
        'SMISMEMBER',
        'SORT_RO',
        'SRANDMEMBER',
        'SSCAN',
        'STRLEN',
        'SUBSTR',
        'SUNION',
        'TOUCH',
        'TTL',
        'TYPE',
        'XINFO',
        'XLEN',
        'XPENDING',
        'XRANGE',
        'XREAD',
        'XREVRANGE',
        'ZCARD',
        'ZCOUNT',
        'ZDIFF',
        'ZINTER',
        'ZINTERCARD',
        'ZLEXCOUNT',
        'ZMSCORE',
        'ZRANDMEMBER',
        'ZRANGE',
        'ZRANGEBYLEX',
        'ZRANGEBYSCORE',
        'ZRANK',
        'ZREVRANGE',
        'ZREVRANGEBYLEX',
        'ZREVRANGEBYSCORE',
        'ZREVRANK',
        'ZSCAN',
        'ZSCORE',
        'ZUNION',
    ];
}
