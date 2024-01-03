
if redis.REDIS_VERSION == nil then
    redis.replicate_commands()
end

local command, results, patterns, cursor, total = nil, {}, {}, 0, 0

if ARGV[#ARGV] == 'use-argv' then
    command = ARGV[2]
    table.insert(patterns, ARGV[1])
else
    command = ARGV[1]
    patterns = KEYS
end

for i, pattern in ipairs(patterns) do
    repeat
        results = redis.call('SCAN', cursor, 'MATCH', pattern, 'COUNT', 500)

        if #results > 1 and #results[2] > 0 then
            for i, key in ipairs(results[2]) do
                redis.call(command, key);
                total = total + 1
            end
        end

        cursor = tonumber(results[1])
    until cursor == 0
end

return total
